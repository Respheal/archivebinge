<?php
	//Start the session
	session_start();
	
	//Remove user data from session
	unset($_SESSION['userdata']);
	
	//Destroy all session data
	session_destroy();
	
	//Redirect to the homepage
	header("Location:index.php");
?>