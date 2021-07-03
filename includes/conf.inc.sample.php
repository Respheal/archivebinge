<?php
$DOMAIN = 'DOMAIN.com'; //The base URL of your Archive Binge installation
$SECRET_KEY = 'SECRET_KEY';

//Your contact email addresses:
//If you intend to reuse the same address for these, consider rewording contact.php, as it treats them all as distinct.
$SUPPORT_EMAIL = 'support@DOMAIN.com';
$FEEDBACK_EMAIL = 'feedback@DOMAIN.com';
$ABUSE_EMAIL = 'abuse@DOMAIN.com';
$TW_HANDLE = '';

//Social Media OAuth Config
$FB_APP_ID = '';     // Facebook App ID
$FB_APP_SECRET = ''; // Facebook App Secret
$GAPP_ID = '';       // Google App Client Id
$GAPP_SECRET = '';   // Google App Client Secret
$TW_KEY = '';        // Twitter Dev Key
$TW_SECRET = '';     // Twitter Secret


/* DO NOT EDIT BELOW THIS POINT */
function dbConnect($connectionType = 'mysqli'){
	$host = 'DATABASE_HOST';
	$db = 'DATABASE_NAME';
	$user = 'DATABASE_USER';
	$pwd = 'DATABASE_PASSWORD';

	if($connectionType == 'mysqli'){
		return new mysqli($host, $user, $pwd, $db);
	} elseif($mysqli->connect_error) {
		die('Connect Error: ' . $mysqli->connect_error());
	} else{
		try{
			return new PDO("mysql:host=$host;dbname=$db", $user, $pwd);
		} catch (PDOException $e){
			echo 'Cannot connect to database';
			exit;
		}
	}
}