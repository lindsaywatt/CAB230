<!DOCTYPE html>
<html>
<?php
	session_start();
	$page_title = "Parkz | Home";
	include 'include/header.php';
?>
<body>

	<!-- Start nav bar -->
	<?php
		$page_name = "index.php";
		include 'include/nav_bar.php';
	?>
	<!-- End nav bar -->
	
	<!-- Start search box section -->
	<section id="search-box">
		<div class="container">
			<!-- Search Form Row -->
			<div class="row">
				<div class="col-xs-12">
					<h1 class="center">Search For Parkz By:</h1>
				</div>
				<div class="col-xs-12">
					<form>
						<div class="row">
							<div class="col-xs-12 col-sm-6 col-md-4">
								<label>Suburb</label>
								<input type="text" name="suburb" id="suburb-input" placeholder="Suburb" list="valid-suburbs" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-4">
								<label>Name</label>
								<div>						
									<input type="text" name="name" id="name-search-field" placeholder="Name" />
									<button id="name-search-button" class="submit">Search</button>
								</div>
							</div>
							<!--begin display rating stars-->
							<div class="col-xs-12 col-sm-6 col-md-3 center">
								<label>Rating</label>
								<div id="rating-stars">
									<!--display empty stars-->
									<i class="fa fa-star-o" id="1"></i>
									<i class="fa fa-star-o" id="2"></i>
									<i class="fa fa-star-o" id="3"></i>
									<i class="fa fa-star-o" id="4"></i>
									<i class="fa fa-star-o" id="5"></i>
								</div>
							</div>
							<!--end display rating stars-->
							<div class="col-xs-12 col-sm-6 col-md-1 no-padding center">
								<label class="block">Near Me</label>
								<button id="location-button"><i class="fa fa-location-arrow"></i></button>
							</div>
							<!--end display location button-->
						</div>
					</form>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-4 center-block status" id="search-status"></div>
			</div>
		</div>
	</section>
	<!-- End search box section -->

	<!-- Start search Results section -->
	<section id="search-results">
		<!--id=dooblidoo in regards to top green banner with overall search information-->
		<h1 id="dooblidoo">Showing parkz near Kelvin Grove</h1>
		<section class="indent-section no-margin">
			<div class="container">
				<div id="map-canvas"></div>
				<div class="row" id="search-results-container">

				</div>
			</div>
		</section>
	</section>
	<!-- End search Results section -->
	<!--begin display loading image-->
	<div class="col-xs-12 center" id="loading">
		<img src="img/loading.gif" alt="loading animation">
	</div>
	<!--end display loading image-->

	<?php
		include 'include/footer.php';
		include 'include/login_window.php';
	?>
	<!--display suburbs in HTML 5 datalist-->
	<datalist id="valid-suburbs">
		<?php
			//include database queries & PDO prepared statements
			include 'include/database_connection.php';
			include 'include/database_library.php';
			//if error: display "no suburbs"
			$suburbs = getAllSuburbs($db);
			if ($suburbs == ERROR) {
				echo "<option>No Suburbs</option>";
			} else {
				//list results as options
				foreach ($suburbs as $suburb) {
					echo "<option>" . ucwords(strtolower($suburb['suburb'])) . "</option>";
				}
			}
		?>
	</datalist>

	<script src="https://maps.googleapis.com/maps/api/js"></script>
	<script type="text/javascript" src="js/main.js"></script>
	<script type="text/javascript" src="js/index.js"></script>

</body>
</html>