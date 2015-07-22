<div class="hide" id="login-popup">
	<i class="fa fa-times" id="popup-close"></i>
	<?php
		if (isset($_SESSION['logged-in'])) {
	?>
			<form id="login-form" action="logout.php">
				<button class="submit" type="submit">Logout</button>
			</form>
	<?php
		} else {
	?>
			<!--begin login form-->
			<form id="login-form" action="login.php">
				<span class="status" id="login-status"></span>
				<input type="text" placeholder="username" name="login-username" id="login-username"/>
				<input type="password" placeholder="password" name="login-password" id="login-password"/>
				<button class="submit" type="submit">Login</button>
				<a href="register.php">Register</a>
			</form>
			<!--end login form-->
	<?php
		}
	?>
</div>
<!--#window blanket greys out screen behind #login-form-->
<div id="window-blanket"></div>