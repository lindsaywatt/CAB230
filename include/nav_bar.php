<?php
	//change login button label to 'logout' if user logged in
	if (isset($_SESSION['logged-in'])) {
		$login_button = "<li><a href='logout.php' data-login='login-button'>Logout</a></li>";
	} else {
		$login_button = "<li><a href='register.php' data-login='login-button'>Login</a></li>";
	}

	$nav_bar = "
	<nav>
		<div class='container'>
			<div class='row'>
				<!--responsive grid column-->
				<div class='col-xs-12 col-md-6 hidden-xs hidden-sm'>
					<a href='http://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '\\') . "' class='nav-icon'>
						<img id='tree-icon' alt='Tree Icon' src='img/tree.png' width='50'>
						<h1 id='title-text'>Parkz</h1>
					</a>
				</div>
				<!--responsive grid column-->
				<div class='col-xs-12 col-md-6'>
					<ul>
						<li><a href='index.php'>Home</a></li>
						<li><a href='profile.php'>Profile</a></li>
						<li><a href='contact.php'>Contact</a></li>
						$login_button
					</ul>
				</div>
			</div>
		</div>
	</nav>";
	//display nav bar
	echo str_replace($page_name, $page_name . '\' class=\'active', $nav_bar);
?>