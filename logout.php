<?php
	session_start();
	unset($_SESSION['logged-in']);
	unset($_SESSION['username']);
	unset($_SESSION['user_id']);
?>