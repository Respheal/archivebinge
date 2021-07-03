<?php
require_once 'includes/conf.inc.php';

if(!session_id()){
    session_start();
}

//Include Twitter client library
include_once 'twitter/twitteroauth.php';

/*
 * Configuration and setup Twitter API
 */
$consumerKey = $TW_KEY;
$consumerSecret = $TW_SECRET;
$redirectURL = 'https://'.$DOMAIN.'/login.php';

?>