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
