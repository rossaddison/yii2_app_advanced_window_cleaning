<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-frontend',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-frontend',
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
            'enableStrictParsing' => false,
            'showScriptName' => false,
            'baseUrl'=>'/',
            'rules' => [
                'your-settings/<action:\w+>/<id:\d+>'=>'company/<action>',
                'work-area/<action:\w+>/<id:\d+>' => 'productcategory/<action>',
                'street/<action:\w+>/<id:\d+>' => 'productsubcategory/<action>',
                'daily-clean/<action:\w+>/<id:\d+>' => 'salesorderheader/<action>',
                'your-staff/<action:\w+>/<id:\d+>' => 'employee/<action>',
                'dateline/<action:\w+>/<id:\d+>' => 'historyline/<action>',
                'house/<action:\w+>/<id:\d+>' => 'product/<action>',
                'specific-cost-main-category-code/<action:\w+>/<id:\d+>' => 'costcategory/<action>',
                'specific-cost-secondary-category-code/<action:\w+>/<id:\d+>' => 'costsubcategory/<action>',
                'individual-cost-under-secoondary-category-code/<action:\w+>/<id:\d+>' => 'cost/<action>',
                'daily-cost-header/<action:\w+>/<id:\d+>' => 'costheader/<action>',
                '<controller:\w+>/<id:\d+>'=>'<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
                '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
                'gii'=>'gii','gii/<controller:\w+>'=>'gii/<controller>',
                'gii/<controller:\w+>/<action:\w+>'=>'gii/<controller>/<action>', 
	    ],
       ],
    ],
    'params' => $params,
];
