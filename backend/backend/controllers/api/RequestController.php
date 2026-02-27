<?php

namespace backend\controllers\api;

use backend\models\Table;
use backend\models\User;
use Yii;
use yii\rest\ActiveController;
use yii\filters\Cors;
use yii\web\Response;
use yii\filters\auth\HttpBearerAuth;
use backend\models\Request;

class RequestController extends ActiveController
{
    public $modelClass = 'backend\models\Request';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Max-Age' => 86400,
                'Access-Control-Expose-Headers' => ['*'],
            ],
        ];
        unset($behaviors['authenticator']);

        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['availability', 'options'],
        ];

        $behaviors['contentNegotiator'] = [
            'class' => 'yii\filters\ContentNegotiator',
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset(
            $actions['index'],
            $actions['create'],
            $actions['update'],
            $actions['delete'],
            $actions['view']
        );
        return $actions;
    }

    public function actionIndex()
    {
        $userId = Yii::$app->user->id;

        $requests = Request::find()
            ->where(['user_id' => $userId])
            ->orderBy(['request_date' => SORT_DESC, 'request_time' => SORT_DESC])
            ->all();

        $result = [];
        foreach ($requests as $request) {
            $result[] = $this->formatRequest($request);
        }

        return [
            'success' => true,
            'data' => $result,
            'count' => count($result),
        ];
    }

    public function actionCreate()
    {
        $httpRequest = Yii::$app->request;

        if (!$httpRequest->isPost) {
            Yii::$app->response->statusCode = 405;
            return ['success' => false, 'message' => 'Метод не разрешен'];
        }

        $userId = Yii::$app->user->id;
        $user = User::findOne($userId);

        if (!$user) {
            Yii::$app->response->statusCode = 404;
            return ['success' => false, 'message' => 'Пользователь не найден'];
        }

        $data = $httpRequest->post();

        $model = new Request();
        $model->user_id = $userId;

        $model->load($data, '');

        $model->contact_name = $model->contact_name ?: ($user->name ?? $user->username);
        $model->contact_phone = $model->contact_phone ?: $user->phone;
        $model->request_type = $model->request_type ?: Request::TYPE_STANDARD;
        $model->special_requests = $model->special_requests ?: '';

        if ($model->request_time && strlen($model->request_time) === 5) {
            $model->request_time .= ':00';
        }

        $model->status = Request::STATUS_PENDING;
        $model->created_at = date('Y-m-d H:i:s');
        $model->updated_at = date('Y-m-d H:i:s');

        if ($model->request_date < date('Y-m-d')) {
            Yii::$app->response->statusCode = 400;
            return ['success' => false, 'message' => 'Нельзя создавать заявки на прошедшие даты'];
        }

        if (
            $model->request_date === date('Y-m-d') &&
            strtotime($model->request_time) <= time()
        ) {
            Yii::$app->response->statusCode = 400;
            return [
                'success' => false,
                'message' => 'На сегодняшний день можно создавать заявки только на будущее время'
            ];
        }

        if ($model->save()) {
            return [
                'success' => true,
                'message' => 'Заявка успешно создана',
                'data' => $this->formatRequest($model),
            ];
        }

        Yii::$app->response->statusCode = 422;
        return [
            'success' => false,
            'message' => 'Ошибка создания заявки',
            'errors' => $model->getErrors(),
        ];
    }


    public function actionView($id)
    {
        $userId = Yii::$app->user->id;

        $request = Request::find()
            ->where(['id' => $id, 'user_id' => $userId])
            ->one();

        if (!$request) {
            Yii::$app->response->statusCode = 404;
            return ['success' => false, 'message' => 'Бронирование не найдено'];
        }

        return [
            'success' => true,
            'data' => $this->formatRequest($request),
        ];
    }

    public function actionCancel($id)
    {
        $userId = Yii::$app->user->id;

        $request = Request::find()
            ->where(['id' => $id, 'user_id' => $userId])
            ->one();

        if (!$request) {
            Yii::$app->response->statusCode = 404;
            return ['success' => false, 'message' => 'Бронирование не найдено'];
        }

        if (!$request->canCancel()) {
            return [
                'success' => false,
                'message' => 'Невозможно отменить бронирование. Либо оно уже отменено/завершено, либо осталось менее 2 часов до времени брони.'
            ];
        }

        $request->status = Request::STATUS_CANCELED;

        if ($request->save()) {
            return [
                'success' => true,
                'message' => 'Бронирование отменено',
                'data' => $this->formatRequest($request),
            ];
        }

        Yii::$app->response->statusCode = 422;
        return [
            'success' => false,
            'message' => 'Ошибка отмены бронирования',
            'errors' => $request->getErrors(),
        ];
    }

    public function actionAvailability()
    {
        $request = Yii::$app->request;

        $date = $request->get('date');
        $time = $request->get('time');
        $guests = $request->get('guests', 2);

        if (!$date || !$time) {
            return ['success' => false, 'message' => 'Укажите дату и время'];
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || strtotime($date) < strtotime(date('Y-m-d'))) {
            return ['success' => false, 'message' => 'Некорректная дата'];
        }

        if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
            return ['success' => false, 'message' => 'Некорректное время'];
        }

        $hour = (int)explode(':', $time)[0];
        $isWorkingHours = $hour >= 12 && $hour <= 23;

        if (!$isWorkingHours) {
            return [
                'success' => true,
                'available' => false,
                'reason' => 'Ресторан работает с 12:00 до 23:00'
            ];
        }

        $allTables = Table::find()
            ->where(['is_active' => 1])
            ->andWhere(['>=', 'capacity', $guests]) 
            ->all();

        $reservedTableIds = Request::find()
            ->select('table_id')
            ->where(['request_date' => $date])
            ->andWhere(['status' => [Request::STATUS_PENDING, Request::STATUS_CONFIRMED]])
            ->andWhere(['<=', 'request_time', $time])
            ->andWhere(['>=', "DATE_ADD(request_time, INTERVAL 2 HOUR)", $time])
            ->column();

        $availableTables = [];
        foreach ($allTables as $table) {
            if (!in_array($table->id, $reservedTableIds)) {
                $availableTables[] = [
                    'id' => $table->id,
                    'number' => $table->number,
                    'name' => $table->name,
                    'capacity' => $table->capacity,
                    'type' => $table->type,
                    'zone' => $table->zone,
                    'description' => $table->description,
                ];
            }
        }

        return [
            'success' => true,
            'data' => [
                'date' => $date,
                'time' => $time,
                'guests' => (int)$guests,
                'available' => !empty($availableTables),
                'available_tables' => $availableTables,
                'available_count' => count($availableTables),
                'total_tables' => count($allTables),
                'reserved_count' => count($reservedTableIds),
            ],
        ];
    }

    private function formatRequest($request)
    {
        return [
            'id' => $request->id,
            'user_id' => $request->user_id,
            'request_date' => $request->request_date,
            'request_time' => $request->request_time,
            'guests_count' => $request->guests_count,
            'contact_name' => $request->contact_name,
            'contact_phone' => $request->contact_phone,
            'request_type' => $request->request_type,
            'special_requests' => $request->special_requests,
            'status' => $request->status,
            'created_at' => $request->created_at,
            'updated_at' => $request->updated_at,
            'can_cancel' => $request->status === Request::STATUS_PENDING,
        ];
    }
}