<?php
	session_start();

	//begin form validation
	include 'include/include.php';
	include 'include/validation.php';
	include 'include/database_connection.php';
	include 'include/database_library.php';

	$status = NOT_POSTED;
	$error_message = "";

	if ($_SERVER["REQUEST_METHOD"] === "POST") {
		validateName("name", $_POST, $error_message);
		validateUsername("username", $_POST, $error_message);
		validateGender("gender", $_POST, $error_message);
		validateEmail("email", $_POST, $error_message);
		validatePassword("password", "confirmpassword", $_POST, $error_message);
		$status = (strlen($error_message) == 0) ? POSTED_NO_ERRORS : POSTED_WITH_ERRORS;
	}

	// display validation criteria on page load
	if ($status == POSTED_NO_ERRORS) {

		$name = $_POST['name'];
		$username = $_POST['username'];
		$gender = $_POST['gender'];
		$email = $_POST['email'];
		$password = $_POST['password'];

		//username field validation
		$username_unique = checkUsernameUnique($db, $username);
		if ($username_unique === ERROR) {
			$status = POSTED_WITH_ERRORS;
			$error_message .= "Database error while checking username<br>";
		} elseif (!$username_unique) {
			$status = POSTED_WITH_ERRORS;
			$error_message .= "The username $username has already been taken<br>";
		}

		//email field validation
		$email_unique = checkEmailUnique($db, $email);
		if ($email_unique === ERROR) {
			$status = POSTED_WITH_ERRORS;
			$error_message .= "Database error while checking email<br>";
		} elseif (!$email_unique) {
			$status = POSTED_WITH_ERRORS;
			$error_message .= "The email address $email has already been registered<br>";
		}

		//register user in database if no errors
		if ($status == POSTED_NO_ERRORS) {
			$register_success = registerUser($db, $name, $email, $gender, $username, $password);
			if ($register_success === ERROR || !$register_success) {
				$status = POSTED_WITH_ERRORS;
				$error_message .= "Database error while registering<br>";
			} else {
				//log user in when successfully registered
				$_SESSION['logged-in'] = true;
				$_SESSION['username'] = $username;
				$_SESSION['user_id'] = getUserID($db, $username);
				//direct user to default page after successful login
				header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]");
			}
		}
	}
	//end form validation
 ?>
<!DOCTYPE html>
<html>
<?php
	$page_title = "Parkz | Register";
	include 'include/header.php';
?>
<body>

	<!-- Start nav bar -->
	<?php
		$page_name = "register.php";
		include 'include/nav_bar.php';
	?>
	<!-- End nav bar -->

	<!--begin display registration form-->
	<section id="register-section">
		<div class="container">
			<div class="row">
				<h1>Sign up here</h1>
				<p class="center no-margin">All fields are required</p>
				<!--responsive grid column-->
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
					if (isset($_SESSION['logged-in'])) {
				?>
						<!--responsive grid column-->
						<div class="col-xs-12">
							<div class="no-reviews">
								<h3>You have registered successfully</h3>
							</div>
						</div>
				<?php
					} else {
				?>		<!--begin registration form-->		
						<form id="register-form" method="post" action="register.php" novalidate>
							<!--responsive grid column-->
							<div class="col-lg-6 center-block">
								<label>Full Name</label><span class="status" id="name-status"></span>
								<input type="text" name="name" placeholder="Full Name" value="<?php prefill('name') ?>" data-validation="req len:0-45 regex:name">

								<label>Username</label><span class="status" id="username-status"></span>
								<input type="text" name="username" placeholder="Username" value="<?php prefill('username') ?>" data-validation="req len:3-15 regex:username">
								<!--responsive grid column-->
								<div class="col-xs-12 no-padding">
									<div class="row">
										<!--responsive grid column-->
										<div class="col-xs-12"><label>Gender</label><span class="status" id="gender-status"></span></div>
										<!--responsive grid column-->
										<div class="col-xs-4 radio-buttons">
											<label>Female</label>
											<input type="radio" name="gender" value="female" <?php prefillRadio('gender', 'female') ?> data-validation="radio:gender">
										</div>
										<!--responsive grid column-->
										<div class="col-xs-4 radio-buttons">
											<label>Male</label>
											<input type="radio" name="gender" value="male" <?php prefillRadio('gender', 'male') ?> data-validation="radio:gender">
										</div>
										<!--responsive grid column-->
										<div class="col-xs-4 radio-buttons">
											<label>Other</label>
											<input type="radio" name="gender" value="other" <?php prefillRadio('gender', 'other') ?> data-validation="radio:gender">
										</div>
									</div>
								</div>

								<label>Email</label><span class="status" id="email-status"></span>
								<input type="email" name="email" placeholder="Email" value="<?php prefill('email') ?>" data-validation="req len:0-50 regex:email">

								<label>Password</label><span class="status" id="password-status"></span>
								<input type="password" name="password" placeholder="Password" value="<?php prefill('password') ?>" data-validation="req len:8-25">

								<label>Confirm Password</label><span class="status" id="confirmpassword-status"></span>
								<input type="password" name="confirmpassword" placeholder="Confirm Password" value="<?php prefill('confirmpassword') ?>" data-validation="req len:8-25 match:password">

								<div class="center">
									<button class="submit" type="submit" id="register-submit">Submit</button>
								</div>
							</div>
						</form>
						<!--begin registration form-->
				<?php
					} 
				?>
			</div>
		</div>
	</section>
		
							

	<?php
		include 'include/footer.php';
		include 'include/login_window.php';
	?>

	<script type="text/javascript" src="js/main.js"></script>
	<script type="text/javascript">
		FormValidation($("#register-form"));
	</script>

</body>
</html>
