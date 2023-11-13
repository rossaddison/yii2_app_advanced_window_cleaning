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
        'clearFrontendCache' => [
            'class' => 'yii\caching\FileCache',
            'cachePath' => Yii::getAlias('@frontend') . '/runtime/cache'
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-frontend'
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
            // @see config/common/
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
       'authManager' => [
            'class' => 'yii\rbac\PhpManager'
       ],   
    ],
    'params' => $params,
    
    'modules' => [
      'google3translateclient'=> [
             'class'=> 'frontend\modules\google3translateclient\Module',  
      ],
      'backuper'=> [
             'class' => 'frontend\modules\backup\Module',
      ],
      'invoice'=>[
             'class' => 'frontend\modules\invoice\application\Module',
      ],    
      'gii' => [
      'class' => 'yii\gii\Module', //adding gii module
        'allowedIPs' => ['127.0.0.1','localhost', '::1'],
                     'generators' => [
                        'migrik' => [
                            'class' => \insolita\migrik\gii\StructureGenerator::class,
                            'templates' => [
                                'custom' => '@frontend/gii/templates/migrator_schema',
                            ],
                        ],
                        'migrikdata' => [
                            'class' => \insolita\migrik\gii\DataGenerator::class,
                            'templates' => [
                                'custom' => '@frontend/gii/templates/migrator_data',
                            ],
                        ],
                    ],
       ],
      'gridview' =>  [
        'class' => '\kartik\grid\Module'
       ],
       'social' => [
         'class' => 'kartik\social\Module',
         'googleAnalytics' => [
            //insert your Google Analytics code here under id.
            //'id' => 'UA-1111111-1',
            'domain' => 'localhost',
            'testMode'=>false,
         ],
        ],
      'treemanager' =>  [
        'class' => 'kartik\tree\Module',
        'treeViewSettings'=> [
            'nodeView' => '@kvtree/views/_form',    
            'nodeAddlViews' => [
                1 => '',
                2 => '',
                3 => '',
                4 => '',
                5 => '@app/views/krajeeproducttree/product',
        ]]    
       ],
       'datecontrol' => [
        'class' => 'kartik\datecontrol\Module',
        'displaySettings' => [
            'date' => 'php:d-M-Y',
            'time' => 'php:H:i:s A',
            'datetime' => 'php:d-m-Y H:i:s A',
        ],
        'saveSettings' => [
            'date' => 'php:Y-m-d', 
            'time' => 'php:H:i:s',
            'datetime' => 'php:Y-m-d H:i:s',
        ],
        // automatically use kartikwidgets for each of the above formats
        'autoWidget' => true,      
     ],
   ],
];
