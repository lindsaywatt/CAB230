<?php
	include 'include/database_connection.php';
	include 'include/database_library.php';
	include 'include/include.php';

	session_start();
	//check username & password fields are not empty 
	if (exists('username', $_POST) && exists('password', $_POST)) {
		$username = $_POST['username'];
		$password = $_POST['password'];
	} else {
		echo "error";
		exit();
	}
	//validate user information
	$vaild_user = checkValidUser($db, $username, $password);
	
	//begin user login
	if ($vaild_user === ERROR || !$vaild_user) {
		echo "error";
	} else {
		$_SESSION['logged-in'] = true;
		$_SESSION['username'] = $username;
		$_SESSION['user_id'] = getUserID($db, $username);
		echo "success";
	}
	//end user login
?>