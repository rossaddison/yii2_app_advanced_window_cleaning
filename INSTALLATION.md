**Installation by means of WAMP :**
Complete absolute path of the VirtualHost folder Examples: C:/wamp/www/project/ or E:/www/site1/ Required is:

C:\wamp64\www\yii2_app_advanced_window_cleaning\frontend_web

using php 8.1.13

**Open with Notepad as administrator:** 
C:\wamp64\bin\apache\apache2.4.54.2\conf\extra\httpd-vhosts.conf"

Included following: 
````
<VirtualHost *:80>
	ServerName wc.myhost
	DocumentRoot "c:/wamp64/www/yii2_app_advanced_window_cleaning/frontend/web"
	<Directory  "c:/wamp64/www/yii2_app_advanced_window_cleaning/frontend/web/">
		RewriteEngine on
                RewriteCond %{REQUEST_FILENAME} !-f
       	        RewriteCond %{REQUEST_FILENAME} !-d
                RewriteRule . index.php
                Options +Indexes +Includes +FollowSymLinks +MultiViews
		AllowOverride All
		Require local
	</Directory>
</VirtualHost>

**Running: php init**

C:\wamp64\www\yii2_app_advanced_window_cleaning>php init
Yii Application Initialization Tool v1.0

Which environment do you want the application to be initialized in?

  [0] Development
  [1] Production

  Your choice [0-1, or "q" to quit] 0
````

frontend/web folder now has an index file after the ````php init```` command has been executed,  and  will now open the first page.

But there is no User table yet so the signup will not run so

1. Create a database in mysql called wc
2. Modify the common\config\main-local.php to include wc.
3. Run the ````yii migrate```` command from the console directory to create

a. the user table used for signing up and logging in.
b. the migration table which will record this migration and create a migration history

````
c:\wamp64\www\yii2_advanced_window_cleaning>yii migrate 
````
First user: Signing up the admin will get user id 1.
Second user: Signing up the observer will get user id 2. 

Typically a paying client will have observer status, able to view an invoice, and make payment.

The above two users have not been assigned any roles with permissions yet.

If you cannot send an email from your localhost in order to activate your login, you can manually go into table user and change the status from 9 to 10.
This eliminates the need to setup your symfonymailer temporarily at common/config/main-local.php

https://stackoverflow.com/questions/33653000/yii2-why-using-status-constant-10-instead-of-1

Include an .htaccess file in frontend/web in WAMP document root ensuring that mod_rewrite is enabled on the server: 

````
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . index.php
# use index.php as index file
DirectoryIndex index.php
````

All requests are redirected to the index.php file.

** Creating role admin with permission edit, and view, and role observer with permission view only **

Create an empty/shell php file; outputting it to console\migrations folder by means of command: 
```` yii migrate/create rbac-init````  

````
C:\wamp64\www\yii2_app_advanced_window_cleaning>yii migrate/create init_rbac
Yii Migration Tool (based on Yii v2.0.50-dev)

Create new migration 'C:\wamp64\www\yii2_app_advanced_window_cleaning\console/migrations\m231101_162925_init_rbac.php'? (yes|no) [no]:yes
New migration created successfully.
````

The empty/shell file m231101_162925_init_rbac.php is now filled with two basic permissions view and edit, and two basic roles, admin, and observer.
The admin can view, and edit.
The observer can view only.

Final file can be found at console/migrations/m231101_162925_init_rbac.php. Note that the up and down functions are named 'up' and 'down'.
This is consistent with the previous two migration files that have already been completed. 

Code for this file as follows:

````
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
        // The signup function in the SiteController has been modified accordingly
         
        $number_of_paying_clients = 5;
        for ($user_id = 2;  $user_id <= ($number_of_paying_clients + 1); $user_id++) {
           $auth->assign($observer, $user_id);
        }

        // User id 1:  admin role with viewPermission, and editPermission permissions 
        $auth->assign($admin, 1);
    }
````

Now actually use this migration file by running the ````yii migrate```` command again.
The yii migrate command will look at the migrations table in the database, see that two migrations have already been run and therefore run the above one
that is outstanding and is not listed in the database.

````
C:\wamp64\www\yii2_app_advanced_window_cleaning>yii migrate
Yii Migration Tool (based on Yii v2.0.50-dev)

Total 1 new migration to be applied:
        m231101_162925_init_rbac

Apply the above migration? (yes|no) [no]:yes
````

After application:
````
*** applying m231101_162925_init_rbac
*** applied m231101_162925_init_rbac (time: 0.032s)
````

Two files are created: 1. assignments.php, 2. items.php located in console/rbac and should appear as follows:

````
**console/rbac/assignments (or user_id => 'role')**

return [
    6 => [
        'observer',
    ],
    5 => [
        'observer',
    ],
    4 => [
        'observer',
    ],
    3 => [
        'observer',
    ],
    2 => [
        'observer',
    ],
    1 => [
        'admin',
    ],
];
````

** console/rbac/items.php **
````

return [
    'editPermission' => [
        'type' => 2,
        'description' => 'Edit any entity',
    ],
    'viewPermission' => [
        'type' => 2,
        'description' => 'View any entity',
    ],
    'admin' => [
        'type' => 1,
        'children' => [
            'editPermission',
            'viewPermission',
        ],
    ],
    'observer' => [
        'type' => 1,
        'children' => [
            'viewPermission',
        ],
    ],
];
````

If your business grows and the number of paying clients exceeds the limit of 5 in the above file, you can then create another migration empty file using 
````
yii migrate/create extra_clients
````
and populate with code similar to the above in it, place it in the migrations folder, and then run the 'yii migrate' command which will pick up
this latest migration file and effectively extend the console/rbac/items.php file with the additional user_id's.

We now need to complete the migrations that are sitting in the frontend/migrations folder by running the command: 

````
yii migrate-db-namespaced
````

This effectively uses the console/config/main.php which has the migrate-db-namespaced mapping.

C:\wamp64\www\yii2_app_advanced_window_cleaning>yii migrate-db-namespaced
Yii Migration Tool (based on Yii v2.0.50-dev)

Total 21 new migrations to be applied:
        frontend\migrations\m191110_221831_Mass
        frontend\migrations\m191207_152415_works_taxDataInsert
        frontend\migrations\m191207_155342_works_instructionDataInsert
        frontend\migrations\m191207_161454_works_importhouses
        frontend\migrations\m200125_075111_carousal_id_fix
        frontend\migrations\m200414_125047_works_companyDataInsert
        frontend\migrations\m200521_152727_works_historyline
        frontend\migrations\m200611_075111_listprice_fix
        frontend\migrations\m200611_152727_add_image_source_filename_column_image_web_filename_column_to_works_product
        frontend\migrations\m200613_215223_works_krajee_product_tree
        frontend\migrations\m200621_152727_add_product_id_column_productsubcategory_id_column_productcategory_id_column_to_works_krajee_product_tree
        frontend\migrations\m200627_075111_product_productnumber_fix_width
        frontend\migrations\m200708_152727_create_productnumber_index_works_product
        frontend\migrations\m200822_212212_session_detail
        frontend\migrations\m210210_160033_add_invoice_id_column_payment_id_column_to_works_salesorderdetail
        frontend\migrations\m210210_204458_Mass
        frontend\migrations\m210303_210035_add_user_id_column_to_works_product
        frontend\migrations\m210501_112535_add_reference_column_to_works_salesinvoice
        frontend\migrations\m210503_215541_works_salesinvoicemethodpayDataInsert
        frontend\migrations\m210503_223834_works_salesinvoiceemailtemplateDataInsert
        frontend\migrations\m210504_012606_works_salesinvoicestatusDataInsert

Apply the above migrations? (yes|no) [no]:yes

** Loading the user data of the two users 'admin' (password:adminadmin) and 'observer' (password:observer) via Unit Testing **

See data file: C:\wamp64\www\yii2_app_advanced_window_cleaning\frontend\tests\unit\fixtures\data\user.php

C:\wamp64\www\yii2_app_advanced_window_cleaning>yii fixture/load User --namespace=frontend\tests\unit\fixtures

Fixtures namespace is:
        frontend\tests\unit\fixtures

Global fixtures will be used:

        1. yii\test\InitDbFixture

Fixtures below will be loaded:

        1. User

Be aware that:
Applying leads to purging of certain data in the database!

Load above fixtures? (yes|no) [no]:yes
Fixtures were successfully loaded from namespace:
        "frontend\tests\unit\fixtures"

        1. yii\test\InitDbFixture
        2. frontend\tests\unit\fixtures\UserFixture 

If you decide to unload this data by means of:

````
C:\wamp64\www\yii2_app_advanced_window_cleaning>yii fixture/load User --namespace=frontend\tests\unit\fixtures
````

remember to change the autoincrement number in phpMyAdmin ... Operations back to 1.

Note: The SiteController's behaviour function has the role set to 'admin' and not '@'. If it was '@' it
would mean that all authenticated users, including the observer role (paying clients) would have access to the signup function 
which we do not want.  We have preset the assignments.php file to one user with admin role and the rest with the observer role 
in the above migration file console/migrations/m231101_162925_init_rbac.php and we do not want this predetermined number of 
potential clients (5 arbitrary) to be 'used up' by indisciminate signing up.

** Loading Product unit test data **

C:\wamp64\www\yii2_app_advanced_window_cleaning>yii fixture/load Product --namespace=frontend\tests\unit\fixtures
Fixtures namespace is:
        frontend\tests\unit\fixtures

Global fixtures will be used:

        1. yii\test\InitDbFixture

Fixtures below will be loaded:

        1. Product

Be aware that:
Applying leads to purging of certain data in the database!

Load above fixtures? (yes|no) [no]:yes
Fixtures were successfully loaded from namespace:
        "frontend\tests\unit\fixtures"

        1. yii\test\InitDbFixture
        2. frontend\tests\unit\fixtures\UserFixture
        3. frontend\tests\unit\fixtures\TaxFixture
        4. frontend\tests\unit\fixtures\ProductcategoryFixture
        5. frontend\tests\unit\fixtures\ProductsubcategoryFixture
        6. frontend\tests\unit\fixtures\ProductFixture

C:\wamp64\www\yii2_app_advanced_window_cleaning>

** Unloading Product unit test data by means of fixtures**

C:\wamp64\www\yii2_app_advanced_window_cleaning>yii fixture/unload Product --namespace=frontend\tests\unit\fixtures

** Building AcceptanceTester.php which is generated into \frontend\tests\_support folder. ** 

Leave it in this folder. The 'codecept run' depends on it in this folder. 

````
C:\wamp64\www\yii2_app_advanced_window_cleaning>php ./vendor/bin/codecept build

Included Configuration: common
Building Actor classes for suites: unit
 -> UnitTesterActions.php generated successfully. 3 methods added
common\tests\UnitTester includes modules: Yii2

Included Configuration: frontend
Building Actor classes for suites: acceptance, functional, unit
 -> AcceptanceTesterActions.php generated successfully. 99 methods added
frontend\tests\AcceptanceTester includes modules: WebDriver, Yii2
AcceptanceTester.php created.
 -> FunctionalTesterActions.php generated successfully. 235 methods added
frontend\tests\FunctionalTester includes modules: Filesystem, Yii2, Asserts
 -> UnitTesterActions.php generated successfully. 148 methods added
frontend\tests\UnitTester includes modules: Yii2, Asserts

Included Configuration: backend
Building Actor classes for suites: functional, unit
 -> FunctionalTesterActions.php generated successfully. 83 methods added
backend\tests\FunctionalTester includes modules: Yii2
 -> UnitTesterActions.php generated successfully. 0 methods added
backend\tests\UnitTester includes modules: 

````

** Running Codecept **

````
C:\wamp64\www\yii2_app_advanced_window_cleaning>php ./vendor/bin/codecept run
````