<?php
	include 'include/database_connection.php';
	include 'include/database_library.php';

	session_start();

	if (isset($_POST['review_id']) && isset($_SESSION['logged-in'])) {
		$delete_success = deleteReview($db, $_POST['review_id'], $_SESSION['user_id']);
		if ($delete_success === ERROR || !$delete_success) {
			echo "error";
		} else {
			echo "success";
		}
	} else {
		echo "error";
	}
?>