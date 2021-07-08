<?php
$DOMAIN = 'DOMAIN.com'; //The base URL of your Archive Binge installation
$SECRET_KEY = 'SECRET_KEY';

// Database Connection
$DB_HOST = '' // Database host, probably localhost
$DB_NAME = '' // Database name
$DB_USER = '' // Database user
$DB_PASS = '' // Database user's password

// Contact Information
//If you intend to reuse the same address for these, consider rewording contact.php, as it treats them all as distinct.
$SUPPORT_EMAIL = 'support@DOMAIN.com';
$FEEDBACK_EMAIL = 'feedback@DOMAIN.com';
$ABUSE_EMAIL = 'abuse@DOMAIN.com';
$TW_HANDLE = '';

// Social Media OAuth Config
$FB_APP_ID = '';     // Facebook App ID
$FB_APP_SECRET = ''; // Facebook App Secret
$GAPP_ID = '';       // Google App Client Id
$GAPP_SECRET = '';   // Google App Client Secret
$TW_KEY = '';        // Twitter Dev Key
$TW_SECRET = '';     // Twitter Secret

// Analytics Config
$GOOGLE_ANALYTICS_ENABLED = False
$GOOGLE_ANALYTICS_ID = '' // Google Analytics Tracking ID

/* DO NOT EDIT BELOW THIS POINT */

function dbConnect($connectionType = 'mysqli'){
	if($connectionType == 'mysqli'){
		return new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
	} elseif($mysqli->connect_error) {
		die('Connect Error: ' . $mysqli->connect_error());
	} else{
		try{
			return new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USER, $DB_PASS);
		} catch (PDOException $e){
			echo 'Cannot connect to database';
			exit;
		}
	}
}
