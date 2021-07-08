<?php require_once("includes/header.inc.php");
require_once("includes/meta.inc.php");
require_once("includes/css.inc.php");

/*testing stuff and prepping security*/
setcookie("test_cookie", "test", time() + 360, '/');
$whitelist = array('token','service','persist');

require_once("includes/formtoken.inc.php");

//if already logged in, go back to home
if(isset($_SESSION) && !empty($_SESSION) && $_SESSION['user_id']){
    if($_SESSION['current_page']){
        header('Location: '.$_SESSION['current_page']);
    }else{
        header('Location: /');
    }
}elseif(empty($_POST['token']) && !isset($_GET['code']) && !isset($_REQUEST['oauth_token'])){
    $newToken = generateFormToken('form1');
}elseif(isset($_POST) && !empty($_POST)){
    foreach ($_POST as $key=>$item) {
        // Check if the value $key (fieldname from $_POST) can be found in the whitelisting array, if not, die with a short message
		if (!in_array($key, $whitelist)) {
			$errors[]="Please use only the fields in the form";
        }
    }

    //if the user checked "Remember me"
    if ($_POST['persist'] != "on") {unset($persist);}else{$persist = "on";}

    if (verifyFormToken('form1') && empty($errors)) {
        //everything is good, call the oauth service
        $state = urlencode(json_encode(array("service"=>$_POST["service"], "persist"=>$persist)));

        if ($_POST['service'] == "Google"){
            require_once('includes/auth/google.settings.php');
            $goto = 'https://accounts.google.com/o/oauth2/auth?scope=profile&redirect_uri=' . urlencode(CLIENT_REDIRECT_URL) . '&response_type=code&client_id=' . CLIENT_ID . '&access_type=online&state='.$state;
        }
        elseif($_POST['service'] == "Facebook"){
            require_once('includes/auth/facebook.settings.php');
            $_SESSION['service'] = "Facebook";
            $loginURL = $helper->getLoginUrl($redirectURL, $fbPermissions);
            $goto = $loginURL;
        }
        elseif($_POST['service'] == "Twitter"){
            require_once('includes/auth/twitter.settings.php');
            //&oauth_callback=http:://localhost:24649/TwitterIdentity/GetTwitterAuthorizationCallback?ForUsername=billgates
            $statequery = '&oauth_callback='.$redirectURL;

            //Fresh authentication
            $twClient = new TwitterOAuth($consumerKey, $consumerSecret);
            $request_token = $twClient->getRequestToken($redirectURL);

            //Received token info from twitter
            $_SESSION['token']         = $request_token['oauth_token'];
            $_SESSION['token_secret']  = $request_token['oauth_token_secret'];

            //If authentication returns success
            if($twClient->http_code == '200'){
                //Get twitter oauth url
                $goto = filter_var($twClient->getAuthorizeURL($request_token['oauth_token']), FILTER_SANITIZE_URL);
            }
        }
        header('Location: '.$goto);
    } else {$errors[]="Malicious entry detected.";}
} elseif(isset($_GET['code']) || (isset($_REQUEST['oauth_token']) && $_SESSION['token'] == $_REQUEST['oauth_token'])) {
    //oauth service has responded with a code

    $conn = dbConnect();

    if($_GET['state']){
        //Google
        $state = json_decode(urldecode($_GET['state']), true);
        $service = trim($state["service"]);
        $persist = $state["persist"];
    }

    if(!$state){
        if($_SESSION['service'] == "Facebook"){$service = "Facebook";}
        else{$service = "Twitter";}
        $persist = "on";
    }

    $token = bin2hex(openssl_random_pseudo_bytes(64));
    if($persist != "on"){unset($persist);}

    if($service == "Google"){
        //google oauth

        try {
            require_once('includes/auth/google-login-api.php');
            require_once('includes/auth/google.settings.php');
            $gapi = new GoogleLoginApi();
            // Get the access token
            $data = $gapi->GetAccessToken(CLIENT_ID, CLIENT_REDIRECT_URL, CLIENT_SECRET, $_GET['code']);

            // Access Tokem
            $access_token = $data['access_token'];

            // Get user information
            $user_info = $gapi->GetUserProfileInfo($access_token);
            $oauthUser = array(
                'oauth_provider'=> $service,
                'oauth_uid'     => $user_info['id'],
                'token'         => $token,
                'persist'       => $persist
            );

        }
        catch(Exception $e) {
            $errors[] = $e->getMessage();
            exit();
        }
    }
    elseif($service == "Facebook"){
        require_once('includes/auth/facebook.settings.php');
        //facebook login
            if(isset($_SESSION['facebook_access_token'])){
                $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
            }else{
                // Put short-lived access token in session
                $_SESSION['facebook_access_token'] = (string) $accessToken;

                // OAuth 2.0 client handler helps to manage access tokens
                $oAuth2Client = $fb->getOAuth2Client();

                // Exchanges a short-lived access token for a long-lived one
                $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);
                $_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;

                // Set default access token to be used in script
                $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
            }

            // Getting user facebook profile info
            try {
                $profileRequest = $fb->get('/me?fields=name');
                $fbUserProfile = $profileRequest->getGraphNode()->asArray();
            } catch(FacebookResponseException $e) {
                $errors[] = 'Graph returned an error: ' . $e->getMessage();
                session_destroy();
                // Redirect user back to app login page
                header("Location: /login.php");
                exit;
            } catch(FacebookSDKException $e) {
                $errors[] = 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }
            $oauthUser = array(
                'oauth_provider'=> $service,
                'oauth_uid'     => $fbUserProfile['id'],
                'token'         => $token,
                'persist'       => $persist
            );
    }
    elseif($service = "Twitter"){
            require_once('includes/auth/twitter.settings.php');
            //Call Twitter API
            $twClient = new TwitterOAuth($consumerKey, $consumerSecret, $_SESSION['token'] , $_SESSION['token_secret']);

            //Get OAuth token
            $access_token = $twClient->getAccessToken($_REQUEST['oauth_verifier']);

            //If returns success
            if($twClient->http_code == '200'){
                //Storing access token data into session
                $_SESSION['status'] = 'verified';
                $_SESSION['request_vars'] = $access_token;

                //Get user profile data from twitter
                $userInfo = $twClient->get('account/verify_credentials');

                //Insert or update user data to the database
                $oauthUser = array(
                    'oauth_provider'=> 'Twitter',
                    'oauth_uid'     => $userInfo->id,
                    'token'         => $token,
                    'persist'       => $persist
                );

                //Remove oauth token and secret from session
                unset($_SESSION['token']);
                unset($_SESSION['token_secret']);

            }else{
                $errors[] = 'Twitter: Some problem occurred, please try again.';
            }
        }
}

if ($oauthUser){
    //print_r($oauthUser);
        require_once('includes/auth/User.php');
        require_once('includes/adjectives.php');
        require_once('includes/animals.php');

        $unadjective = array_rand($adjectives);
        $unanimal = array_rand($animals);

        $oauthUser['user_name'] = $adjectives[$unadjective]." ".$animals[$unanimal];
        $oauthUser['claim'] = uniqid("ab_", true);

        $user = new User($conn);
        $user_db_query = $user->checkUser($oauthUser);
        $userData = $user_db_query[0];
        $errors = $user_db_query[1];
        if(!$errors){
            if(count($_COOKIE) > 0 && $persist) {
                $cookie_name = "abUser";
                $cookie_data = array($userData['user_id'], $token);

                $iv_size = openssl_cipher_iv_length("aes-256-ctr");
                $iv = openssl_random_pseudo_bytes($iv_size);
                $cookie_value = openssl_encrypt(json_encode($cookie_data),"aes-256-ctr",$SECRET_KEY, 0, $iv);
                $encrypted_cookie = $iv.$cookie_value;

                setcookie($cookie_name, $encrypted_cookie, time() + (86400 * 30 * 3), "/");
                session_destroy();
            }else{
                $_SESSION['user_id'] = $userData['user_id'];
            }
        }

        if ($userData['user_id']){
            if($_SESSION['current_page']){
                header('Location: '.$_SESSION['current_page']);
            }else{
                header('Location: /');
            }
        }

}
?>
<style>.btn-group.special {
  display: flex;
  margin-bottom:15px;
}

.special .btn {
  flex: 1
}
.progress{
    margin:0;
    border-radius:0;
}

.dashboard-comic {
    border-top-left-radius: 4px;
    border-top-right-radius: 4px;
}
</style>
  </head>
  <body>

<?php require_once 'includes/menu.inc.php';?>

<div class="container panel panel-default">
    <div class="panel-body">
        <div class="row">
<?php
if(!count($_COOKIE) > 0) {echo'<div class="row">
<div class="col-md-6 col-md-offset-3">
    <div class="alert alert-warning" role="alert">Your browser does not allow cookies. Your login session will expire when the window closes.<a class="alert-link" href="https://www.whatismybrowser.com/guides/how-to-enable-cookies/">How to Enable Cookies.</a></div>
</div>
</div>';}
if($_GET['error']){
    echo'<div class="row">
<div class="col-md-6 col-md-offset-3">
    <div class="alert alert-warning" role="alert">';
    if($_GET['error'] == 1){echo 'Error : Failed to receieve access token';}
    elseif($_GET['error'] == 2){echo 'Error : Failed to get user information';}
    else{echo 'Everything broke. I don\'t know how. Send me details at <a href="mailto:'.$SUPPORT_EMAIL.'">'.$SUPPORT_EMAIL.'</a> and know that you have bested me.';}
    echo '</a></div>
</div>
</div>';}
if(is_array($errors) && count($errors) > 0) {echo'<div class="row">
<div class="col-md-6 col-md-offset-3">';
    if(count($errors > 1)) {
        foreach ($errors as $error){
            echo'    <div class="alert alert-danger" role="alert">Error: '.$error.'</div>';
        }
    }else{
        echo'    <div class="alert alert-danger" role="alert">Error: '.$error.'</div>';
    }
echo '</div>
</div>';
}?>
<div class="row">
    <div class="col-md-4 col-md-offset-4">
        <h1>Login</h1>
        <div class="alert alert-info">
          <strong>Important Note:</strong> Your first login will generate a random username. You may edit the username on your Profile page once the account is created.
        </div>
        <form class="form-group" action="login.php" method="post">
            <input type="hidden" name="token" value="<?php echo $newToken; ?>">
                    <div class="btn-group special" role="group" aria-label="Subscription Controls">

            <input type="submit" class="btn btn-lg btn-google" name="service" value="Google">
            <input type="submit" class="btn btn-lg btn-facegoose" name="service" value="Facebook">
            <input type="submit" class="btn btn-lg btn-socialbird" name="service" value="Twitter">
            </div>
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="persist"> Remember Me
                </label>
            </div>
			<p class="help-block">If social media buttons are greyed out, try <a href="https://kb.iu.edu/d/ahic">clearing your cache</a> (ctrl+f5). If you don't see all three social media options, try temporarily disabling ad-block.</p>
            <a href="/tos">Terms of Service</a> | <a href="/privacy">Privacy Policy</a>
        </form>
    </div>
</div>

    	</div><!--row-->
    </div><!--panel body-->
</div> <!-- /container -->

	<?php require_once 'includes/footer.inc.php';?>
  </body>
</html>
