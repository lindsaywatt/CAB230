<?php
	session_start();

	include 'include/validation.php';
	include 'include/database_connection.php';
	include 'include/database_library.php';

	$status = NOT_POSTED;
	$error_message = "";

	$logged_in = isset($_SESSION["logged-in"]);
	$is_admin = false;

	if ($logged_in) {
		$is_admin = checkIsAdmin($db, $_SESSION["user_id"]);
		if ($is_admin === ERROR) {
			$error_message = "Database error checking admin status";
			$is_admin = false;
		}
	}

	if ($_SERVER["REQUEST_METHOD"] === "POST") {
		if (isset($_FILES['csv'])){
			$file_name = explode('.', $_FILES['csv']['name']);
			$file_ext = strtolower(end($file_name));
			if ($file_ext === 'csv' && $logged_in && $is_admin) {
				$status = POSTED_NO_ERRORS;
			} else {
				$status = POSTED_WITH_ERRORS;
				$error_message = "Only CSV files accepted";
			}
		} else {
			$status = POSTED_WITH_ERRORS;
			$error_message = "CSV file not recieved";
		}
	}

	if ($status == POSTED_NO_ERRORS) {
		$file_tmp = $_FILES['csv']['tmp_name'];
		if (deleteAllParks($db) !== ERROR) {
			$ptr = fopen($file_tmp, "r");
			$stmt = $db->prepare(ADD_PARK_QUERY);
			$row = fgetcsv($ptr);
			while (($row = fgetcsv($ptr)) !== FALSE) {
				try {
					$stmt->execute($row);
				} catch (PDOException $ex) {
					$error_message = "Database error while adding new parks";
					echo $ex->getMessage();
					$status = POSTED_WITH_ERRORS;
					break;
				}
			}
			fclose($ptr);
		} else {
			$status = POSTED_WITH_ERRORS;
			$error_message = "Database error while removing parks";
		}
	}
?>
<!DOCTYPE html>
<html>
<?php
	$page_title = "Parkz | Admin";
	include 'include/header.php';
?>
<body>

	<!-- Start nav bar -->
	<?php
		$page_name = "admin.php";
		include 'include/nav_bar.php';
	?>
	<!-- End nav bar -->
	
	<section id="contact-section">
		<div class="container">
			<div class="row">
				<h1>Admin Page</h1>
				<div class="col-xs-12 center">
					<div class="col-xs-12 col-sm-6 center-block center" id="register-status">
						<!--begin 'display error details'-->
						<?php
							if (strlen($error_message) != 0) {
						?>
								<span class="status status-error">
									<?php echo $error_message; ?>
								</span>
						<?php
							}
						?>
						<!--end display error details-->
					</div>
					<?php
						if ($status == POSTED_NO_ERRORS) {
					?>
							<div class="col-xs-12">
								<div class="no-reviews">
									<h3>New dataset successfully uploaded</h3>
								</div>
							</div>
					<?php
						}

						if ($is_admin && $logged_in) {
					?>
							<p>Upload a new park dataset</p>
							<form action="" method="POST" enctype="multipart/form-data">
								<input type="file" name="csv">
								<button class="submit" type="submit">Upload</button>
							</form>
					<?php
						} else {
					?>
							<div class="col-xs-12">
								<div class="no-reviews">
									<h3>You are not authorised to see this page</h3>
								</div>
							</div>
					<?php
						}
					?>
				</div>
			</div>
		</div>
	</section>

	<?php
		include 'include/footer.php';
		include 'include/login_window.php';
	?>
	
	<script type="text/javascript" src="js/main.js"></script>
</body>
</html>