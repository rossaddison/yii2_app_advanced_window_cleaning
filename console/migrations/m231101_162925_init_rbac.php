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
        
        // Estimated number of paying clients that you as administrator will signup
        // Only a logged in administrator will be able to signup and send an activation email to the client
        // or manually change the user status in the user table from 9 to 10 which is quite tedious
        // The signup facility will not be exposed to the public
        // The signup function in the SiteController has been modified accordingly to only authorize the admin to perform the signing up
         
        $number_of_paying_clients = 5;
        for ($user_id = 2;  $user_id <= ($number_of_paying_clients + 1); $user_id++) {
           $auth->assign($observer, $user_id);
        }

        // User id 1:  admin role with viewPermission, and editPermission permissions 
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
