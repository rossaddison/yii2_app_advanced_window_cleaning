**Installation by means of WAMP :**
Complete absolute path of the VirtualHost folder Examples: C:/wamp/www/projet/ or E:/www/site1/ Required is:

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

frontend/web now has an index file after the ````php init```` command has been exectued and  will open the first page.

But there is no User table yet so the signup will not run so

1. Create a database in mysql called wc
2. Modify the common\config\main-local.php to include wc.
3. run the ````yii migrate```` command from the console directory to create
a. the user table used for signing up and logging in.
b. the migration table which will record this migration and create a migration history

