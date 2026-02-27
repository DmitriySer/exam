<?php
namespace backend\controllers\api;

use Yii;
use yii\rest\Controller;
use yii\filters\Cors;

class ApiController extends Controller
{
    public function behaviors()
    {

        $behaviors = parent::behaviors();

        unset($behaviors['authenticator']);

        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['http://localhost'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 86400,
            ],
        ];

        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['options', 'test'],
        ];

        return $behaviors;
    }

    public function actionOptions()
    {
        Yii::$app->response->statusCode = 200;
        return [];
    }

    public function actionTest()
    {
        return [
            'success' => true,
            'message' => 'problems',
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => Yii::$app->request->method,
            'headers' => [
                'origin' => Yii::$app->request->headers->get('Origin'),
                'content_type' => Yii::$app->request->headers->get('Content-Type'),
            ],
            'cors_configured' => true,
        ];
    }

    public function actionTestParam($name = 'default')
    {
        return [
            'success' => true,
            'message' => 'params',
            'param' => $name,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }
}