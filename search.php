<?php
	//geolocation distance function variables
	$radius = 1e3;
	$earth_radius = 6371e3;

	include 'include/include.php';
	include 'include/database_connection.php';
	include 'include/database_library.php';	

	$type = exists('type', $_GET) ? $_GET["type"] : "";
	$search_string = exists('query', $_GET) ? $_GET["query"] : "";

	//search criteria switch
	$query = "";
	switch ($type) {
		case 'suburb':
			$raw_results = searchBySuburb($db, $search_string);
			break;
		case 'name':
			$raw_results = searchByName($db, $search_string);
			break;
		case 'rating':
			$raw_results = searchByRating($db, $search_string);
			break;
		case 'location':
			$raw_results = searchByLocation($db);
			break;
		case 'top-parkz':
			$raw_results = getTopParkz($db, $search_string);
			break;
		default:
			echo "error";
			exit();
	}

	$results = array();

	//search by location (geolocation)
	if ($type === 'location') {
		//seperate lat & lon with ',' as a seperator
		list($lat, $lon) = explode(',', $search_string, 2);
		$lat = deg2rad(floatval($lat));
		$lon = deg2rad(floatval($lon));
		// filter out results outside the radius (15km)
		foreach ($raw_results as $row) {
			// find the distance on the surface of the earth between user and the park location
			$lat2 = deg2rad($row['lat']);
			$lon2 = deg2rad($row['lon']);
			$a = pow(sin(($lat - $lat2)/2), 2) + cos($lat) * cos($lat2) * pow(sin(($lon - $lon2)/2), 2);
			$c = 2 * atan2(sqrt($a), sqrt(1-$a));
			$dist = $earth_radius * $c;
			if ($dist < $radius) {
				$row['dist'] = round($dist, 0);
				array_push($results, $row);
			}
		}
		//sort results by distance ascending
		$results = shellSort($results, 'dist');
	} else {
		$results = $raw_results;
	}

	$json = array("search_results" => array(), "num_results" => count($results));

	// print out results
	if (count($results) > 0) {
		foreach ($results as $row) {
			$name = ucwords(strtolower($row['name']));
			$street = ucwords(strtolower($row['street']));
			//responsive grid column
			$html = "<div class='col-xs-12 col-sm-6 col-lg-3'>
						<div class='park-result'>
						<!--direct to park details page (specified by park id in URL-->
							<a href='park.php?id=$row[park_id]'>
								<!--begin result header-->
								<div class='result-head'>
									<h3>$name</h3>
									<!--if search by distance, specify how far away park is-->
									<small>$street" . (isset($row['dist']) ? (" (" . $row['dist'] . 'm away from you)') : "") . "</small>
								</div>
								<!--end result header-->
								<!--begin result body-->
								<div class='result-body'>
									<img src='" . get_random_image() . "' alt='Park'>
									<!--display average rating of park-->
									<div class='result-rating' title='Average Rating: $row[average_rating]'>
										<!--round average rating to one decimal place-->
										<!--display half full star for ratings with decimal places-->
										<span>" . round($row['average_rating'], 1) . "<i class='fa fa-star-half-o'></i></span>"
										. make_rating_stars($row['average_rating']) .
										//print num stars of rounded average rating
										"<span>" . $row['num_ratings'] . "<i class='fa fa-user'></i></span>
									</div>
								</div>
								<!--end result body-->
							</a>
						</div>
					</div>";
			$json_object = array(
				"id" => $row['park_id'],
				"html" => $html,
				"name" => $name,
				"street" => $street,
				"lat" => floatval($row['lat']),
				"lon" => floatval($row['lon'])
			);
			array_push($json['search_results'], $json_object);
		}
		header('Content-Type: application/json');
		echo json_encode($json);
	} else {
		echo "error";
	}

?>