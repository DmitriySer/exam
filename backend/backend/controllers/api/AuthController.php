<?php
namespace backend\controllers\api;

use backend\models\User;
use Yii;
use yii\filters\Cors;
use yii\rest\Controller;
use yii\web\Response;

class AuthController extends ApiController
{
    public $modelClass = 'backend\models\User';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        if (isset($behaviors['authenticator'])) {
            $behaviors['authenticator']['except'] = array_merge(
                isset($behaviors['authenticator']['except']) ? $behaviors['authenticator']['except'] : [],
                ['login', 'register', 'admin-login', 'options', 'me', 'logout']
            );
        }

        return $behaviors;
    }

    public function actionOptions()
    {
        Yii::$app->response->statusCode = 200;
    }

    public function actionRegister()
    {
        $request = Yii::$app->request;

        if (!$request->isPost) {
            Yii::$app->response->statusCode = 405;
            return ['success' => false, 'message' => 'Метод не разрешен.'];
        }

        $rawData = $request->getRawBody();

        $data = json_decode($rawData, true);


        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = $request->post();
            Yii::info("Using post() data instead", 'api');
        }

        $requiredFields = ['username', 'email', 'password'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                Yii::$app->response->statusCode = 400;
                return ['success' => false, 'message' => "Обязательное поле '$field' отсутствует."];
            }
        }

        if (User::findByUsername($data['username'])) {
            Yii::$app->response->statusCode = 409;
            return ['success' => false, 'message' => 'Пользователь с таким именем уже существует.'];
        }

        if (User::findByEmail($data['email'])) {
            Yii::$app->response->statusCode = 409;
            return ['success' => false, 'message' => 'Пользователь с таким email уже существует.'];
        }

        $user = new User();
        $user->scenario = User::SCENARIO_REGISTER;

        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->setPassword($data['password']);
        $user->generateAuthKey();

        if (isset($data['phone'])) $user->phone = $data['phone'];
        if (isset($data['first_name'])) $user->first_name = $data['first_name'];
        if (isset($data['last_name'])) $user->last_name = $data['last_name'];

        $user->created_at = time();
        $user->updated_at = time();
        $user->status = User::STATUS_ACTIVE;

        if ($user->save()) {
            Yii::$app->user->login($user);

            return [
                'success' => true,
                'message' => 'Регистрация прошла успешно.',
                'token' => $user->auth_key,
                'user' => $this->getUserData($user),
            ];
        } else {
            Yii::$app->response->statusCode = 422;
            return [
                'success' => false,
                'message' => 'Ошибка валидации данных.',
                'errors' => $user->getErrors(),
            ];
        }
    }

    public function actionLogin()
    {
        $request = Yii::$app->request;

        if (!$request->isPost) {
            Yii::$app->response->statusCode = 405;
            return ['success' => false, 'message' => 'Метод не разрешен.'];
        }

        $data = $request->post();

        if (empty($data['login']) || empty($data['password'])) {
            Yii::$app->response->statusCode = 400;
            return ['success' => false, 'message' => 'Логин и пароль обязательны.'];
        }

        $user = User::findByEmail($data['login']);
        if (!$user) {
            $user = User::findByUsername($data['login']);
        }

        if (!$user || !$user->validatePassword($data['password'])) {
            Yii::$app->response->statusCode = 401;
            return ['success' => false, 'message' => 'Неверный логин или пароль.'];
        }

        if ($user->status != User::STATUS_ACTIVE) {
            Yii::$app->response->statusCode = 403;
            return ['success' => false, 'message' => 'Аккаунт не активен.'];
        }

        Yii::$app->user->login($user);

        return [
            'success' => true,
            'message' => 'Вход выполнен успешно.',
            'token' => $user->auth_key,
            'user' => $this->getUserData($user),
        ];
    }


    public function actionMe()
    {
        $token = Yii::$app->request->get('token') ?:
            Yii::$app->request->post('token') ?:
                Yii::$app->request->headers->get('Authorization');

        // Убираем Bearer если есть
        if ($token && stripos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }

        if (!$token) {
            Yii::$app->response->statusCode = 401;
            return ['success' => false, 'message' => 'Токен не предоставлен.'];
        }

        $user = User::find()->where(['auth_key' => $token])->one();

        if (!$user) {
            Yii::$app->response->statusCode = 401;
            return ['success' => false, 'message' => 'Неверный токен.'];
        }

        return [
            'success' => true,
            'user' => $this->getUserData($user),
        ];
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return ['success' => true, 'message' => 'Выход выполнен успешно.'];
    }

    private function getUserData($user)
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => trim($user->first_name . ' ' . $user->last_name),
            'status' => $user->status,
            'created_at' => date('Y-m-d H:i:s', $user->created_at),
            'updated_at' => date('Y-m-d H:i:s', $user->updated_at),
        ];
    }

    public function actionAdminLogin()
    {
        $request = Yii::$app->request;

        if (!$request->isPost) {
            Yii::$app->response->statusCode = 405;
            return ['success' => false, 'message' => 'Метод не разрешен.'];
        }

        $data = $request->post();

        if (empty($data['login']) || empty($data['password'])) {
            Yii::$app->response->statusCode = 400;
            return ['success' => false, 'message' => 'Логин и пароль обязательны.'];
        }

        $user = User::findByEmail($data['login']);
        if (!$user) {
            $user = User::findByUsername($data['login']);
        }

        if (!$user || !$user->validatePassword($data['password'])) {
            Yii::$app->response->statusCode = 401;
            return ['success' => false, 'message' => 'Неверный логин или пароль.'];
        }

        if ($user->status != User::STATUS_ACTIVE) {
            Yii::$app->response->statusCode = 403;
            return ['success' => false, 'message' => 'Аккаунт не активен.'];
        }

        if (!$user->isAdmin()) {
            Yii::$app->response->statusCode = 403;
            return [
                'success' => false,
                'message' => 'Доступ запрещен. Требуются права администратора.',
                'user_type' => 'user'
            ];
        }

        Yii::$app->user->login($user);

        return [
            'success' => true,
            'message' => 'Вход администратора выполнен успешно.',
            'token' => $user->auth_key,
            'user' => $this->getUserData($user),
            'is_admin' => true,
        ];
    }
}