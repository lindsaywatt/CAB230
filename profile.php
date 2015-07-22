<!DOCTYPE html>
<html>
<?php
	session_start();
	$logged_in = isset($_SESSION['logged-in']);
	
	$page_title = "Parkz | Profile";
	include 'include/header.php';
?>
<body>

	<!-- Start nav bar -->
	<?php
		$page_name = "profile.php";
		include 'include/nav_bar.php';
		include 'include/include.php';
		include 'include/database_connection.php';
		include 'include/database_library.php';

		$welcome_message = "Your Profile";
		if ($logged_in) {
			$welcome_message = "Hello " . getUserFullName($db, $_SESSION['user_id']);
		}
	?>

	<section id="contact-section">
		<div class='container'>
			<div class='row'>
				<h1><?php echo $welcome_message ?></h1>
			<?php
				$num_reviews = 0;
				if ($logged_in) {
					$reviews = getReviewsByUser($db, $_SESSION['user_id']);
					if ($reviews !== ERROR) {
						$num_reviews = count($reviews);
						if ($num_reviews > 0) {
							foreach ($reviews as $review) {
			?>
								<div class="col-xs-12 no-padding">
									<div class="review">
										<div class="row">
											<div class="col-xs-6">
												<h3><?php echo "<a href='park.php?id=$review[park_id]'>" . ucwords(strtolower($review['park_name'])) . "</a>"; ?></h3>
											</div>
											<div class="col-xs-6">
												<!--display stars as rating-->
												<div class="review-rating" title="Rating: <?php echo $review['rating'] ?>">
													<?php echo make_rating_stars($review['rating']) ?>
												</div>
											</div>
											<!--display review text-->
											<div class="col-xs-12 col-sm-10 review-text">
												<p><?php echo $review['review_text'] ?></p>
											</div>
											<div class="col-xs-12 col-sm-2 review-text">
												<button class="submit delete-button" id="<?php echo $review['review_id'] ?>">Delete</button>
											</div>
											<!--display review date-->
											<div class="col-xs-12 col-sm-12 review-date">
												<span>
													<?php
														echo "Posted review on " . date('M jS, Y \a\t g:ia', strtotime($review['review_date']));
														echo " - Visited park on " . date('M jS, Y', strtotime($review['date_visited']));
												 	?>
												</span>
											</div>
										</div>
									</div>
								</div>
					<?php
							}
						} else {
					?>
							<div class="col-xs-12">
								<div class="no-reviews">
									<h3>You have not left any reviews yet</h3>
								</div>
							</div>
			<?php
						}
					} else {
			?>
						<div class="col-xs-12">
							<div class="no-reviews">
								<h3>Something went wrong ay</h3>
							</div>
						</div>
			<?php
					}
				} else {
			?>
					<div class="col-xs-12">
						<div class="no-reviews">
							<h3><a data-login="login-button">Login to see your profile</a></h3>
						</div>
					</div>
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
	<script type="text/javascript" src="js/profile.js"></script>
</body>
</html>

