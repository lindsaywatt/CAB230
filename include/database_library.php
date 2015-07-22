<?php
	define("ERROR", "error");
	define("SEARCH_LIMIT", 100);

	/* Start database queries */
	define("CHECK_VALID_USER", "SELECT * FROM users WHERE username = :username AND password = SHA2(CONCAT(:password, salt), 0)");
	define("CHECK_IS_ADMIN_QUERY", "SELECT admin FROM users WHERE id = :user_id");
	define("ALL_SUBURBS_QUERY", "SELECT suburb FROM parkz GROUP BY suburb ORDER BY suburb ASC");
	define("PARK_DETAILS_QUERY", "SELECT id, name, street, suburb, lat, lon FROM parkz WHERE id = :id LIMIT 1");
	define("PARK_REVIEWS_QUERY", "SELECT reviews.id, reviews.rating, review_text, name, review_date, date_visited FROM reviews LEFT JOIN users ON reviews.user_id = users.id WHERE park_id = :id ORDER BY review_date DESC");
	define("ALL_PARKS_QUERY", "SELECT name, street, suburb, parkz.id AS 'park_id', lat, lon, count(rating) AS 'num_ratings', avg(rating) AS 'average_rating' FROM parkz LEFT JOIN reviews ON parkz.id=reviews.park_id GROUP BY parkz.id ORDER BY name ASC");
	define("REGISTER_USER_QUERY", "INSERT INTO users (username, email, password, name, gender, salt) VALUES (:username, :email, SHA2(CONCAT(:password, :salt),0), :name, :gender, :salt)");
	define("CHECK_USERNAME_UNIQUE_QUERY", "SELECT * FROM users WHERE username LIKE :username");
	define("CHECK_EMAIL_UNIQUE_QUERY", "SELECT * FROM users WHERE email LIKE :email");
	define("SUBURB_SEARCH_QUERY", "SELECT name, street, suburb, parkz.id AS 'park_id', lat, lon, count(rating) AS 'num_ratings', avg(rating) AS 'average_rating'
						 FROM (
						 	SELECT * FROM parkz
						 	WHERE suburb LIKE :query
						 ) AS parkz LEFT JOIN reviews ON parkz.id=reviews.park_id
						 GROUP BY parkz.id ORDER BY average_rating DESC LIMIT " . SEARCH_LIMIT);
	define("NAME_SEARCH_QUERY", "SELECT name, street, suburb, parkz.id AS 'park_id', lat, lon, count(rating) AS 'num_ratings', avg(rating) AS 'average_rating'
						 FROM (
						 	SELECT * FROM parkz
						 	WHERE name LIKE :query
						 ) AS parkz LEFT JOIN reviews ON parkz.id=reviews.park_id
						 GROUP BY parkz.id ORDER BY name ASC LIMIT " . SEARCH_LIMIT);

	define("RATING_SEARCH_QUERY", "SELECT name, street, suburb, parkz.id AS 'park_id', lat, lon, park_ids.average AS 'average_rating', num_ratings
							FROM (
								SELECT * FROM (
									SELECT park_id, AVG(rating) AS average, COUNT(rating) AS num_ratings FROM reviews GROUP BY park_id
								) AS averages WHERE average >= :query
							) AS park_ids LEFT JOIN parkz ON parkz.id = park_ids.park_id ORDER BY average DESC LIMIT " . SEARCH_LIMIT);
	define("TOP_PARKZ_SEARCH_QUERY", "SELECT name, street, suburb, parkz.id AS 'park_id', lat, lon, count(rating) AS 'num_ratings', avg(rating) AS 'average_rating' FROM parkz LEFT JOIN reviews ON parkz.id=reviews.park_id GROUP BY parkz.id ORDER BY AVG(rating) DESC LIMIT :limit");
	define("LOCATION_SEARCH_QUERY", "SELECT name, street, suburb, parkz.id AS 'park_id', lat, lon, count(rating) AS 'num_ratings', avg(rating) AS 'average_rating' FROM parkz LEFT JOIN reviews ON parkz.id=reviews.park_id GROUP BY parkz.id");
	define("ADD_REVIEW_QUERY", "INSERT INTO reviews (park_id, user_id, rating, review_text, date_visited) VALUES (:park_id, :user_id, :rating, :review_text, :date_visited)");
	define("GET_USER_ID_QUERY", "SELECT id FROM users WHERE username = :username");
	define("GET_USER_FULL_NAME_QUERY", "SELECT name FROM users WHERE id = :user_id");
	define("GET_REVIEWS_BY_USER_QUERY", "SELECT reviews.id as 'review_id', parkz.id as 'park_id', parkz.name as 'park_name', reviews.rating as 'rating', review_text, review_date, date_visited FROM reviews LEFT JOIN parkz ON reviews.park_id = parkz.id WHERE user_id = :user_id");
	define("CHECK_USER_ALREADY_REVIEWED_QUERY", "SELECT * FROM reviews WHERE user_id = :user_id AND park_id = :park_id");
	define("DELETE_REVIEW_QUERY", "DELETE FROM reviews WHERE id = :id AND user_id = :user_id");
	define("ADD_PARK_QUERY", "INSERT INTO parkz (id, park_code, name, street, suburb, easting, northing, lat, lon) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
	define("ADD_CONTACT_MESSAGE_QUERY", "INSERT INTO contact (name, email, phone, subject, message) VALUES (:name, :email, :phone, :subject, :message)");
	/* End database queries */

	/* Start database functions */
	// Retrieve all unique suburbs
	function getAllSuburbs($db) {
		try {
			$results = $db->query(ALL_SUBURBS_QUERY);
			return $results->fetchAll();
		} catch (PDOException $ex) {
			return ERROR;
		}
	}

	//retrieve all park information using the ALL_PARKS_QUERY
	//Data in Query:
	//park name, street, suburb, lat, lon, avg rating, count of ratings
	function getAllParks($db) {
		try {
			$results = $db->query(ALL_PARKS_QUERY);
			return $results->fetchAll();
		} catch (PDOException $ex) {
			return ERROR;
		}
	}

	//retrieve all park details using the PARK_DETAILS_QUERY
	//Data in Query:
	//park id, name, street, suburb, lat, lon
	function getParkDetails($db, $park_id) {
		try {
			$stmt = $db->prepare(PARK_DETAILS_QUERY);
			$stmt->execute(array(':id' => $park_id));
			$park = $stmt->fetch(PDO::FETCH_OBJ);
			return $park;
		} catch (PDOException $ex) {
			return ERROR;
		}
	}

	// retrieve all park review information in the PARK_REVIEWS_QUERY
	//Data in Query:
	//reviews.id, reviews.rating, avg rating, count of ratings, review_text, name, review_date, date_visited, username
	function getParkReviews($db, $park_id) {
		try {
			$stmt = $db->prepare(PARK_REVIEWS_QUERY);
			$stmt->execute(array(':id' => $park_id));
			$reviews = $stmt->fetchAll();
			return $reviews;
		} catch (PDOException $ex) {
			return ERROR;
		}
	}

	function getReviewsByUser($db, $user_id) {
		try {
			$stmt = $db->prepare(GET_REVIEWS_BY_USER_QUERY);
			$stmt->execute(array(':user_id' => $user_id));
			$reviews = $stmt->fetchAll();
			return $reviews;
		} catch (PDOException $ex) {
			return ERROR;
		}
	}

	function deleteReview($db, $review_id, $user_id) {
		try {
			$stmt = $db->prepare(DELETE_REVIEW_QUERY);
			$stmt->execute(array(':id' => $review_id, ':user_id' => $user_id));
			return ($stmt->rowCount() == 1);
		} catch (PDOException $ex) {
			return ERROR;
		}
	}

	//insert values into reviews & users table in database
	function addReview($db, $user_id, $park_id, $rating, $review, $date_visited) {
		try {
			$stmt = $db->prepare(ADD_REVIEW_QUERY);
			$stmt->execute(
				array(':user_id' => $user_id,
					  ':park_id' => $park_id,
					  ':rating' => $rating,
					  ':review_text' => $review,
					  ':date_visited' => $date_visited)
			);
			return ($stmt->rowCount() == 1);
		} catch (PDOException $ex) {
			return ERROR;
		}
	}

	function userAlreadyReviewed($db, $user_id, $park_id) {
		try {
			$stmt = $db->prepare(CHECK_USER_ALREADY_REVIEWED_QUERY);
			$stmt->execute(array(':user_id' => $user_id, ':park_id' => $park_id));
			return (count($stmt->fetchAll()) > 0);
		} catch (PDOException $ex) {
			return ERROR;
		}	
	}

	function addContactMessage($db, $name, $email, $phone, $subject, $message) {
		try {
			$stmt = $db->prepare(ADD_CONTACT_MESSAGE_QUERY);
			$stmt->execute(
				array(':name' => $name,
					  ':email' => $email,
					  ':phone' => $phone,
					  ':subject' => $subject,
					  ':message' => $message)
			);
			return ($stmt->rowCount() == 1);
		} catch (PDOException $ex) {
			return ERROR;
		}
	}

	//query database for parks in specified suburb
	//Data in Query:
	//park name, street, suburb, lat, lon, avg rating for each park, count of ratings for each park
	function searchBySuburb($db, $search_string) {
		try {
			$stmt = $db->prepare(SUBURB_SEARCH_QUERY);
			$stmt->execute(array(':query' => '%' . strtoupper($search_string) . '%'));
			return $stmt->fetchAll();
		} catch (PDOException $ex) {
			return ERROR;
		}
	}

	//query database for parks matching string in name field 
	//Data in Query:
	//park name, street, suburb, lat, lon, avg rating for each park, count of ratings for each park
	function searchByName($db, $search_string) {
		try {
			$stmt = $db->prepare(NAME_SEARCH_QUERY);
			$stmt->execute(array(':query' => '%' . strtoupper($search_string) . '%'));
			return $stmt->fetchAll();
		} catch (PDOException $ex) {
			return ERROR;
		}
	}

	//query database for parks with a rating of specified value
	//Data in Query:
	//park name, street, suburb, lat, lon, avg rating for each park, count of ratings for each park
	function searchByRating($db, $search_string) {
		try {
			$stmt = $db->prepare(RATING_SEARCH_QUERY);
			$stmt->execute(array(':query' => $search_string));
			return $stmt->fetchAll();
		} catch (PDOException $ex) {
			return ERROR;
		}
	}

	//query database by specified location value
	//Data in Query:
	//park name, street, suburb, lat, lon, avg rating for each park, count of ratings for each park
	function searchByLocation($db) {
		try {
			return $db->query(LOCATION_SEARCH_QUERY);
			return $stmt->fetchAll();
		} catch (PDOException $ex) {
			return ERROR;
		}
	}

	function getTopParkz($db, $limit) {
		try {
			$stmt = $db->prepare(TOP_PARKZ_SEARCH_QUERY);
			$stmt->bindValue(':limit', intval($limit), PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll();
		} catch (PDOException $ex) {
			return $ex->getMessage();
		}
	}

	//query database for user specified username
	function checkUsernameUnique($db, $username) {
		try {
			$stmt = $db->prepare(CHECK_USERNAME_UNIQUE_QUERY);
			$stmt->execute(array(':username' => $username));
			return (count($stmt->fetchAll()) == 0);
		} catch (PDOException $ex) {
			return ERROR;
		}
	}

	//query database for user specified email
	function checkEmailUnique($db, $email) {
		try {
			$stmt = $db->prepare(CHECK_EMAIL_UNIQUE_QUERY);
			$stmt->execute(array(':email' => $email));
			return (count($stmt->fetchAll()) == 0);
		} catch (PDOException $ex) {
			return ERROR;
		}
	}

	//insert user info into users table
	function registerUser($db, $name, $email, $gender, $username, $password) {
		try {
			$stmt = $db->prepare(REGISTER_USER_QUERY);
			$stmt->execute(
				array(':name' => $name,
					  ':username' => $username,
					  ':gender' => $gender,
					  ':email' => $email,
					  ':password' => $password,
					  ':salt' => uniqid())
			);
			return ($stmt->rowCount() == 1);
		} catch (PDOException $ex) {
			return ERROR;
		}
	}

	//check username & password combination are valid
	function checkValidUser($db, $username, $password) {
		try {
			$stmt = $db->prepare(CHECK_VALID_USER);
			$stmt->execute(
				array(':username' => $username,
					  ':password' => $password)
			);
			return (count($stmt->fetchAll()) == 1) ? "success" : ERROR;
		} catch (PDOException $ex) {
			return ERROR;
		}
	}

	function checkIsAdmin($db, $user_id) {
		try {
			$stmt = $db->prepare(CHECK_IS_ADMIN_QUERY);
			$stmt->execute(array(':user_id' => $user_id));
			return ($stmt->fetch(PDO::FETCH_OBJ)->admin == 1);
		} catch (PDOException $ex) {
			return ERROR;
		}
	}	

	//retrieve user ID from specified username
	function getUserID($db, $username) {
		try {
			$stmt = $db->prepare(GET_USER_ID_QUERY);
			$stmt->execute(array(':username' => $username));
			return $stmt->fetch(PDO::FETCH_OBJ)->id;
		} catch (PDOException $ex) {
			return ERROR;
		}
	}

	function getUserFullName($db, $user_id) {
		try {
			$stmt = $db->prepare(GET_USER_FULL_NAME_QUERY);
			$stmt->execute(array(':user_id' => $user_id));
			return $stmt->fetch(PDO::FETCH_OBJ)->name;
		} catch (PDOException $ex) {
			return ERROR;
		}
	}

	function deleteAllParks($db) {
		try {
			$stmt = $db->prepare("SET FOREIGN_KEY_CHECKS = 0");
			$stmt->execute();
			$stmt = $db->prepare("TRUNCATE TABLE parkz");
			$stmt->execute();
			$stmt = $db->prepare("SET FOREIGN_KEY_CHECKS = 1");
			$stmt->execute();
			return $stmt->errorCode();
		} catch (PDOException $ex) {
			return ERROR;
		}	
	}
	/* End database functions */

?>