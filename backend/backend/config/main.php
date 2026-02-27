<?php

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'user' => [
            'identityClass' => 'backend\models\User',
            'enableAutoLogin' => true,
            'enableSession' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'session' => [
            'name' => 'advanced-backend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                'GET api/test' => 'api/api/test',
                'POST api/test' => 'api/api/test',
                'OPTIONS api/test' => 'api/api/options',
                'GET api/test/<name>' => 'api/api/test-param',

                'POST api/auth/admin-login' => 'api/auth/admin-login',

                'POST api/auth/register' => 'api/auth/register',
                'POST api/auth/login' => 'api/auth/login',
                'POST api/auth/logout' => 'api/auth/logout',
                'GET api/auth/me' => 'api/auth/me',

                'GET api/requests' => 'api/request/index',
                'POST api/requests' => 'api/request/create',
                'GET api/requests/<id:\d+>' => 'api/request/view',

                'POST api/requests/<id:\d+>/cancel' => 'api/request/cancel',

                'GET api/requests/availability' => 'api/request/availability',
            ],
        ],
    ],
    'params' => $params,
];