<?php
	/* Make connection to database */
	$db = new PDO('mysql:host=localhost;dbname=database_name', 'username', 'password');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>