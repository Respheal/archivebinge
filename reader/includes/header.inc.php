<?php
ob_start();
session_start();
require_once("../includes/conf.inc.php");

function truncate($text, $chars = 250) {
    $text = $text." ";
    $text = substr($text,0,$chars);
    $text = substr($text,0,strrpos($text,' '));
    $text = $text."...";
    return $text;
}

$conn = dbConnect();
$date = date('Y-m-d H:i:s');

if (basename($_SERVER['SCRIPT_FILENAME'],'.php') != 'login'){
    $_SESSION['current_page'] = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}
echo "<!-- ".$_SESSION['current_page']."  -->";

if(isset($_COOKIE["abUser"]) && !isset($_SESSION['user_id'])) {
    
    $iv_size = openssl_cipher_iv_length("aes-256-ctr");
    $iv = substr($_COOKIE["abUser"], 0, $iv_size);
    $cookie = json_decode(openssl_decrypt(substr($_COOKIE["abUser"], $iv_size), "aes-256-ctr", $SECRET_KEY, 0, $iv));
    $token = $cookie[1];
    
    $stmt = $conn->prepare("select user_id from user_cookies where token = ?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $stmt->bind_result($uid);
    $stmt->store_result();
    $stmt->fetch();

    if ($uid) {
        //kill the cookie that auth'd this session
        $stmt = $conn->prepare("delete from user_cookies where token = ? and user_id = ?");
        $stmt->bind_param('si', $token, $uid);
        $stmt->execute();
        //make a new one
        $token = bin2hex(openssl_random_pseudo_bytes(64));
        $cookie_data = array($_SESSION["user_id"], $token);

        $iv_size = openssl_cipher_iv_length("aes-256-ctr");
        $iv = openssl_random_pseudo_bytes($iv_size);

        $cookie_value = openssl_encrypt(json_encode($cookie_data),"aes-256-ctr",$SECRET_KEY, 0, $iv);
        $encrypted_cookie = $iv.$cookie_value;
        setcookie("abUser", $encrypted_cookie, time() + (86400 * 30 * 3), "/");
        
        //Insert user data
        
        $stmt = $conn->prepare("INSERT INTO user_cookies (user_id, token, created) VALUES (?, ?, ?)");
        $stmt->bind_param('iss', $uid, $token, $date);
        $stmt->execute();
        $_SESSION['user_id'] = $uid;
		
		//set site settings
		
		$stmt = $conn->prepare("select site_settings from users where user_id = ?");
		$stmt->bind_param('i', $uid);
		$stmt->execute();
		$stmt->bind_result($site_settings);
		$stmt->store_result();
		$stmt->fetch();
		
		if ($site_settings) {
			$site_settings = json_decode($site_settings, True);
			$_SESSION['reader_options'] = $site_settings["reader_options"];
            $_SESSION['frame_position'] = $site_settings["frame_position"];
            $_SESSION['hide_faves'] = $site_settings["hide_faves"];
		}
    } else {
        setcookie("abUser", '', time()-86400, '/');
    }
    $stmt->close();
}

$conn->close();
