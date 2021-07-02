<?php
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
$secretkey = 'SECRET_KEY';
$DOMAIN = 'DOMAIN.com'; //The base URL of your Archive Binge installation

//Your contact email addresses:
//If you intend to reuse the same address for these, consider rewording contact.php, as it treats them all as distinct.
$SUPPORT_EMAIL = 'support@DOMAIN.com';
$FEEDBACK_EMAIL = 'feedback@DOMAIN.com';
$ABUSE_EMAIL = 'abuse@DOMAIN.com';
