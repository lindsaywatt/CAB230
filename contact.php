<?php
	session_start();

	include 'include/include.php';
	include 'include/validation.php';
	include 'include/database_connection.php';
	include 'include/database_library.php';

	$status = NOT_POSTED;
	$error_message = "";

	if ($_SERVER["REQUEST_METHOD"] === "POST") {
		validateName('name', $_POST, $error_message);
		validateEmail('email', $_POST, $error_message);
		validatePhone('phone', $_POST, $error_message);
		validateSubject('subject', $_POST, $error_message);
		validateMessage('message', $_POST, $error_message);
		$status = (strlen($error_message) == 0) ? POSTED_NO_ERRORS : POSTED_WITH_ERRORS;
	}

	if ($status == POSTED_NO_ERRORS) {
		$contact_sucess = addContactMessage($db, $_POST['name'], $_POST['email'], $_POST['phone'], $_POST['subject'], $_POST['message']);
		if ($contact_sucess === ERROR || $contact_sucess == 0) {
			$status = POSTED_WITH_ERRORS;
			$error_message = "Database error while adding message...";
		} else {
			$_SESSION['message-sent'] = true;
			header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]");
			exit();
		}
	}
?>
<!DOCTYPE html>
<html>
<?php
	$page_title = "Parkz | Contact";
	include 'include/header.php';
?>
<body>

	<!-- Start nav bar -->
	<?php
		$page_name = "contact.php";
		include 'include/nav_bar.php';
	?>
	<!-- End nav bar -->
	
	<!-- Contact section -->
	<section id="contact-section">
		<div class="container">
			<div class="row">
				<h1>Enter Your Details</h1>
				<p class="center no-margin">Fields with * are required</p>
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
					if (isset($_SESSION['message-sent'])) {
						unset($_SESSION['message-sent']);
				?>
						<div class="col-xs-12">
							<div class="no-reviews">
								<h3>Successfully sent message</h3>
							</div>
						</div>
				<?php
					} else {
				?>
						<form id="contact-form" method="post" novalidate>
							<div class="col-xs-12 col-md-6 center-block">
								<label>full name*</label><span class="status" id="name-status"></span>
								<input type="text" name="name" placeholder="full name" value="<?php prefill('name') ?>" data-validation="req len:0-45 regex:name" />

								<label>email*</label><span class="status" id="email-status"></span>
								<input type="email" name="email" placeholder="email" value="<?php prefill('email') ?>" data-validation="req len:0-50 regex:email" />

								<label>phone</label><span class="status" id="phone-status"></span>
								<input type="tel" name="phone" placeholder="phone" value="<?php prefill('phone') ?>" data-validation="len:0-15 regex:phone" />

								<label>subject</label><span class="status" id="subject-status"></span>
								<input type="text" name="subject" placeholder="subject" value="<?php prefill('subject') ?>" data-validation="len:0-100" />

								<label>your message*</label><span class="status" id="message-status"></span>
								<textarea name="message" placeholder="message" data-validation="req len:0-1000"><?php prefill('message') ?></textarea>

								<div class="center">
									<button class="submit" type="submit" id="contact-submit">Submit</button>
								</div>
							</div>
						</form>
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
		FormValidation($("#contact-form"));
	</script>

</body>
</html>