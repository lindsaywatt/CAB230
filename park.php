<?php
//park details page
	session_start();

	include 'include/include.php';
	include 'include/validation.php';
	include 'include/database_connection.php';
	include 'include/database_library.php';

	if (exists('id', $_REQUEST)) {
		$park_id = $_REQUEST['id'];
	} else {
		echo "no park id requested";
		exit();
	}

	$logged_in = isset($_SESSION['logged-in']);
	$already_reviewed = false;
	$user_id = 0;

	$status = NOT_POSTED;
	$error_message = "";

	if ($logged_in) {
		$user_id = $_SESSION['user_id'];
		$already_reviewed = userAlreadyReviewed($db, $user_id, $_REQUEST['id']);
		if ($already_reviewed) {
			$error_message = "You have already left a review for this park";
		}
	}

	//start park details page review validation
	//review information validation
	if ($_SERVER["REQUEST_METHOD"] === "POST") {
		if ($logged_in) {
			validateID('id', $_POST, $error_message);
			validateRating('review-rating', $_POST, $error_message);
			validateReview('review-text', $_POST, $error_message);
			validateDateVisited('review-date-visited', $_POST, $error_message);
			$status = (strlen($error_message) == 0) ? POSTED_NO_ERRORS : POSTED_WITH_ERRORS;
		}
	}

	if ($status == POSTED_NO_ERRORS) {
		//post review
		$review_success = addReview($db, $user_id, $_POST['id'], $_POST['review-rating'], $_POST['review-text'], $_POST['review-date-visited']);
		if ($review_success === ERROR || $review_success == 0) {
			$status = POSTED_WITH_ERRORS;
			$error_message = "Database error while adding review...";
		} else {
			//reload page and display review if successful
			header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]?id=$_POST[id]");
		}
	}
	//end park details page review validation

	$park = getParkDetails($db, $park_id);
	$park_name = ucwords(strtolower($park->name));
	$park_street = ucwords(strtolower($park->street));
	$park_suburb = ucwords(strtolower($park->suburb));
	$park_lat = $park->lat;
	$park_lon = $park->lon;

	//retrieve park review information
	$reviews = getParkReviews($db, $park_id);
	if ($reviews === ERROR || count($reviews) == 0) {
		$num_reviews = 0;
	} else {
		$num_reviews = count($reviews);
	}
	//retrieve park average
	$park_average = 0;
	if ($num_reviews > 0) {
		$sum = 0;
		foreach ($reviews as $review) {
			$sum += $review['rating'];
		}
		$park_average = round($sum / $num_reviews, 1);
	}	
?>
<!DOCTYPE html>
<html>
<?php
	$page_title = $park_name; 
	include 'include/header.php';
?>
<body>

	<!-- Start nav bar -->
	<?php
		$page_name = "park.php";
		include 'include/nav_bar.php';
	?>
	<!-- End nav bar -->

	<!-- Park details section -->
	<section id="park-details-section">
		<div class="container">
			<div id="map-canvas"></div>
			<div class="detail-head">
				<div class="row">
					<div class="col-xs-12 col-md-8">
						<h2><?php echo $park_name ?></h2>
						<h3><?php echo $park_street ?></h3>
					</div>
					<div class="col-xs-12 col-md-4">
						<div class="park-rating" title="Average Rating: <?php echo $park_average ?>">
							<!--print half full star for park average (float value)-->
							<span><?php echo $park_average ?><i class='fa fa-star-half-o'></i></span>
								 <?php echo make_rating_stars($park_average) ?>
								 <!--print full stars for rounded park average-->
							<span><?php echo $num_reviews ?><i class='fa fa-user'></i></span>
						</div>
					</div>
				</div>
			</div>

			<!-- Reviews -->
			<div class="row">
			<!--start display reviews-->	
			<?php
				if ($num_reviews > 0) {
					foreach ($reviews as $review) {
			?>
						<div class="col-xs-12">
							<div class="review">
								<div class="row">
									<div class="col-xs-6 col-sm-3 col-md-2">
										<h3><?php echo $review['name'] ?></h3>
									</div>
									<div class="col-xs-6 col-sm-3 col-md-2">
										<!--display stars as rating-->
										<div class="review-rating" title="Rating: <?php echo $review['rating'] ?>">
											<?php echo make_rating_stars($review['rating']); ?>
										</div>
									</div>
									<!--display review text-->
									<div class="col-xs-12 review-text">
										<p><?php echo $review['review_text'] ?></p>
									</div>
									<!--display review date-->
									<div class="col-xs-12 review-date">
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
			<!--end display reviews-->
			<?php

					}
				} else {
			?>
			
					<!--display if no reviews-->
					<div class="col-xs-12">
						<div class="no-reviews">
							<h3>No Reviews yet</h3>
						</div>
					</div>
			<?php
				}

				if ($logged_in) {
					if (!$already_reviewed) {
			?>
						<!--begin display 'leave a review' section-->
						<!--responsive grid-->
						<div class="col-xs-12">
							<div class="review">
								<form id="review-form" method="post" action="park.php" onsubmit="return addExtraData()" novalidate>
									<div class="row">
										<div class="col-xs-12 center">
											<h3>Leave a review</h3>
											<?php
												if (strlen($error_message) != 0) {
											?>
													<span class="status status-error">
														<?php echo $error_message; ?>
													</span>
											<?php
												}
											?>
										</div>
										<div class="col-xs-12 col-sm-6 center">
											<label>Your rating for this park</label>
											<!--display blank stars for user to specify rating-->
											<div class="review-rating" id="rating-stars" title="Enter a rating">
												<?php echo make_rating_stars(prefillRating('review-rating')); ?>
											</div>
										</div>
										<div class="col-xs-12 col-sm-6">
											<!--include date type field to specify date visited-->
											<label>Date you visited this park</label><span class="status" id="review-date-visited-status"></span>
											<input type="date" name="review-date-visited" id="review-date" value="<?php prefill("review-date-visited") ?>" data-validation="req regex:date">
										</div>
										<div id="extra-data"></div>
										<div class="col-xs-12">
											<!--input for user to leave a review-->
											<label>Review</label><span class="status" id="review-text-status"></span>
											<textarea id="review-text" name="review-text" placeholder="Type your review here" data-validation="req len:0-1000"><?php prefill("review-text") ?></textarea>
											<button id="review-button" class="submit" type="submit">Submit</button>
										</div>
									</div>
								</form>
							</div>
						</div>
			<?php
					} else {
			?>
						<div class="col-xs-12">
							<div class="no-reviews">
								<h3>You have already left a review</h3>
							</div>
						</div>
			<?php
					}
				} else {
			?>
					<!--display if not logged in-->
					<div class="col-xs-12">
						<div class="no-reviews">
							<h3><a data-login="login-button">Login to leave a review</a></h3>
						</div>
					</div>
			<?php
				}
			?>
			</div>
			<!-- End Reviews -->
		</div>
	</section>
	<!-- End park details section -->

	<?php
		include 'include/footer.php';
		include 'include/login_window.php';
	?>

	<script src="https://maps.googleapis.com/maps/api/js"></script>
	<script type="text/javascript" src="js/main.js"></script>
	<script type="text/javascript">
		var lat = <?php echo $park_lat ?>;
		var lon = <?php echo $park_lon ?>;
		var name = "<?php echo $park_name ?>";
		var street = "<?php echo $park_street ?>";
		var park_id = <?php echo $park_id ?>;
	</script>
	<?php
		if ($logged_in && !$already_reviewed) {
	?>		
		<script type="text/javascript" src="js/park_review.js"></script>
	<?php
		}
	?>	
	<script type="text/javascript" src="js/park.js"></script>

</body>
</html>