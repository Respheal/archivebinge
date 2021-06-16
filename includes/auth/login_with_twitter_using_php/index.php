<?php
//start session
session_start();

//Include Twitter config file && User class
include_once 'twConfig.php';
include_once 'User.php';

//If OAuth token not matched
if(isset($_REQUEST['oauth_token']) && $_SESSION['token'] !== $_REQUEST['oauth_token']){
	//Remove token from session
	unset($_SESSION['token']);
	unset($_SESSION['token_secret']);
}

//If user already verified 
if(isset($_SESSION['status']) && $_SESSION['status'] == 'verified' && !empty($_SESSION['request_vars'])){
	//Retrive variables from session
	$username 		  = $_SESSION['request_vars']['screen_name'];
	$twitterId		  = $_SESSION['request_vars']['user_id'];
	$oauthToken 	  = $_SESSION['request_vars']['oauth_token'];
	$oauthTokenSecret = $_SESSION['request_vars']['oauth_token_secret'];
	$profilePicture	  = $_SESSION['userData']['picture'];
	
	/*
	 * Prepare output to show to the user
	 */
	$twClient = new TwitterOAuth($consumerKey, $consumerSecret, $oauthToken, $oauthTokenSecret);
	
	//If user submits a tweet to post to twitter
	if(isset($_POST["updateme"])){
		$my_update = $twClient->post('statuses/update', array('status' => $_POST["updateme"]));
	}
	
	//Display username and logout link
	$output = '<div class="welcome_txt">Welcome <strong>'.$username.'</strong> (Twitter ID : '.$twitterId.'). <a href="logout.php">Logout</a>!</div>';
	
	//Display profile iamge and tweet form
	$output .= '<div class="tweet_box">';
	$output .= '<img src="'.$profilePicture.'" width="120" height="110"/>';
	$output .= '<form method="post" action=""><table width="200" border="0" cellpadding="3">';
	$output .= '<tr>';
	$output .= '<td><textarea name="updateme" cols="60" rows="4"></textarea></td>';
	$output .= '</tr>';
	$output .= '<tr>';
	$output .= '<td><input type="submit" value="Tweet" /></td>';
	$output .= '</tr></table></form>';
	$output .= '</div>';
	
	//Get latest tweets
	$myTweets = $twClient->get('statuses/user_timeline', array('screen_name' => $username, 'count' => 5));
	
	//Display the latest tweets
	$output .= '<div class="tweet_list"><strong>Latest Tweets : </strong>';
	$output .= '<ul>';
	foreach($myTweets  as $tweet){
		$output .= '<li>'.$tweet->text.' <br />-<i>'.$tweet->created_at.'</i></li>';
	}
	$output .= '</ul></div>';
}elseif(isset($_REQUEST['oauth_token']) && $_SESSION['token'] == $_REQUEST['oauth_token']){
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

		//Initialize User class
		$user = new User();
		
		//Insert or update user data to the database
		$name = explode(" ",$userInfo->name);
		$fname = isset($name[0])?$name[0]:'';
		$lname = isset($name[1])?$name[1]:'';
		$profileLink = 'https://twitter.com/'.$userInfo->screen_name;
		$twUserData = array(
			'oauth_provider'=> 'twitter',
			'oauth_uid'     => $userInfo->id,
			'first_name'    => $fname,
			'last_name'     => $lname,
			'email'         => '',
			'gender'        => '',
			'locale'        => $userInfo->lang,
			'picture'       => $userInfo->profile_image_url,
			'link'          => $profileLink,
			'username'		=> $userInfo->screen_name
		);
		
		$userData = $user->checkUser($twUserData);
		
		//Storing user data into session
		$_SESSION['userData'] = $userData;
		
		//Remove oauth token and secret from session
		unset($_SESSION['token']);
		unset($_SESSION['token_secret']);
		
		//Redirect the user back to the same page
		header('Location: ./');
	}else{
		$output = '<h3 style="color:red">Some problem occurred, please try again.</h3>';
	}
}else{
	//Fresh authentication
	$twClient = new TwitterOAuth($consumerKey, $consumerSecret);
	$request_token = $twClient->getRequestToken($redirectURL);
	
	//Received token info from twitter
	$_SESSION['token']		 = $request_token['oauth_token'];
	$_SESSION['token_secret']= $request_token['oauth_token_secret'];
	
	//If authentication returns success
	if($twClient->http_code == '200'){
		//Get twitter oauth url
		$authUrl = $twClient->getAuthorizeURL($request_token['oauth_token']);
		
		//Display twitter login button
		$output = '<a href="'.filter_var($authUrl, FILTER_SANITIZE_URL).'"><img src="images/sign-in-with-twitter.png" width="151" height="24" border="0" /></a>';
	}else{
		$output = '<h3 style="color:red">Error connecting to twitter! try again later!</h3>';
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Login with Twitter using PHP by CodexWorld</title>
	<link rel='stylesheet' type='text/css' href='style.css'>
</head>
<body>
	<!-- Display login button / profile information -->
	<?php echo $output; ?>
</body>
</html>