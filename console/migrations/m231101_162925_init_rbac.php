<?php

use yii\db\Migration;

/**
 * Class m231101_162925_init_rbac
 */
class m231101_162925_init_rbac extends Migration
{
    /**
     * @see https://www.yiiframework.com/doc/guide/2.0/en/security-authorization#using-migrations
     * {@inheritdoc}
     */
    public function up()
    {
        $auth = Yii::$app->authManager;

        // add "editPermission" permission
        $editPermission = $auth->createPermission('editPermission');
        $editPermission->description = 'Edit any entity';
        $auth->add($editPermission);
        
        // add "viewPermission" permission
        $viewPermission = $auth->createPermission('viewPermission');
        $viewPermission->description = 'View any entity';
        $auth->add($viewPermission);

        $admin = $auth->createRole('admin');
        $auth->add($admin);
        $auth->addChild($admin, $editPermission);
        $auth->addChild($admin, $viewPermission);
        
        $observer = $auth->createRole('observer');
        $auth->add($observer);
        $auth->addChild($observer, $viewPermission);
        
        $auth->assign($observer, 2);
        $auth->assign($admin, 1);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        echo "m231101_162925_init_rbac cannot be reverted.\n";

        return false;
    }
}
