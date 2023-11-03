<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-console',
    'name'=> 'multi-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'timezone' => 'UTC',
    'controllerNamespace' => 'console\controllers',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\console\controllers\FixtureController',
            'namespace' => 'common\fixtures',
          ],
         'migrate-db-namespaced' => [
                'class' => 'yii\console\controllers\MigrateController',
                'migrationNamespaces' => [  
                        //frontend has been aliased in common/config/bootstrap.php which appears in web/index.php
                        //installs the works tables and inserts data
                        'frontend\migrations',
                ],
               'color'=>true,
               'comment'=> 'You are migrating the namespaced tables to database connection component db which is your administration database.',
               'db' => 'db',
               'interactive'=>1,
               'migrationPath' => null, // allows to disable not namespaced migration completely
          ],
    ],
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],  
        ],
    ],
    'params' => $params,
    'modules' => [
            'backuper'=> [
                  'class' => 'frontend\modules\backup\Module',
            ],
      ],
];
