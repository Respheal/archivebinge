<?php
require_once 'includes/conf.inc.php';

/* Google App Client Id */
define('CLIENT_ID', $GAPP_ID);

/* Google App Client Secret */
define('CLIENT_SECRET', $GAPP_SECRET);

/* Google App Redirect Url */
define('CLIENT_REDIRECT_URL', 'https://'.$DOMAIN.'/login.php');

?>