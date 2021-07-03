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
$consumerKey = '';
$consumerSecret = '';
$redirectURL = 'https://'.$DOMAIN.'/login.php';

?>
