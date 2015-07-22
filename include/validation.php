<?php
	define("NOT_POSTED", 0);
	define("POSTED_WITH_ERRORS", 1);
	define("POSTED_NO_ERRORS", 2);
	define("PHONE_REGEX", "/^[0-9+ \-]*$/");
	define("DATE_REGEX", "/^\d{4}-\d{2}-\d{2}$/");
	define("NAME_REGEX", "/^[a-zA-Z \-']*$/");
	define("USERNAME_REGEX", "/^[a-zA-Z0-9_\.!?-]*$/");
	define("EMAIL_REGEX", "/[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/");

	function validateName($name, $array, &$errors) {
		if (!exists($name, $array)) {
			$errors .= "Please enter your full name<br>";
		} elseif (strlen($array[$name]) > 40) {
			$errors .= "Full name must be less than 41 characters<br>";
		} elseif (!preg_match(NAME_REGEX, $array[$name])) {
			$errors .= "Full name contains invalid characters<br>";
		}
	}

	function validateGender($name, $array, &$errors) {
		if (!exists($name, $array)) {
			$errors .= "Please select a gender<br>";
		} elseif (!in_array($array[$name], array("male", "female", "other"))) {
			$errors .= "Please select a valid gender<br>";
		}
	}

	function validateUsername($name, $array, &$errors) {
		if (!exists($name, $array)) {
			$errors .= "Please enter a username<br>";
		} elseif (strlen($array[$name]) < 3) {
			$errors .= "Username must have more than 2 characters<br>";
		} elseif (strlen($array[$name]) > 15) {
			$errors .= "Username must have less than 16 characters<br>";
		} elseif (!preg_match(USERNAME_REGEX, $array[$name])) {
			$errors .= "Username contains invalid characters<br>";
		}
	}	

	function validatePassword($name, $match, $array, &$errors) {
		if (!exists($name, $array)) {
			$errors .= "Please enter a password<br>";
		} elseif (strlen($array[$name]) < 8) {
			$errors .= "Password must be longer than 7 characters<br>";
		} elseif (strlen($array[$name]) > 25) {
			$errors .= "Password must be less than 26 characters<br>";
		}
		if (!exists($match, $array)) {
			$errors .= "Please confirm your password";
		} elseif ($array[$match] !== $array[$name]) {
			$errors .= "Passwords do not match";
		}
	}

	function validateEmail($name, $array, &$errors) {
		if (!exists($name, $array)) {
			$errors .= "Please enter an email address<br>";
		} elseif (strlen($array[$name]) > 50) {
			$errors .= "Email must be less than 51 characters<br>";
		} elseif (!preg_match(EMAIL_REGEX, $array[$name])) {
			$errors .= "Invalid email address<br>";
		}
	}

	function validateRating($name, $array, &$errors) {
		if (!exists($name, $array)) {
			$errors .= "Please provide a rating<br>";
		} else {
			$rating = intval($array[$name]);
			if ($rating <= 0 || $rating > 5) {
				$error_message .= "Rating must be between 1-5<br>";
			}
		}
	}

	function validateReview($name, $array, &$errors) {
		if (!exists($name, $array)) {
			$errors .= "Please provide a review<br>";
		} elseif (strlen($array[$name]) > 1000) {
			$errors .= "Review must be less than 1001 characters<br>";
		}
	}

	function validateSubject($name, $array, &$errors) {
		if (exists($name, $array)) {
			if (strlen($array[$name]) > 100) {
				$errors .= "Subject must be less than 101 characters<br>";
			}
		}
	}

	function validatePhone($name, $array, &$errors) {
		if (exists($name, $array)) {
			if (strlen($array[$name]) > 60) {
				$errors .= "Phone must be less than 61 characters<br>";
			} elseif (!preg_match(PHONE_REGEX, $array[$name])) {
				$errors .= "Phone contains invalid characters<br>";
			}
		}
	}	

	function validateMessage($name, $array, &$errors) {
		if (!exists($name, $array)) {
			$errors .= "Please provide a message<br>";
		} elseif (strlen($array[$name]) > 1000) {
			$errors .= "Message must be less than 1001 characters<br>";
		}
	}

	function validateDateVisited($name, $array, &$errors) {
		if (!exists($name, $array)) {
			$errors .= "Please provide a date visited<br>";
		} elseif (!preg_match(DATE_REGEX, $array[$name])) {
			$errors .= "Date must be formatted like YYYY-mm-dd<br>";
		} else {
			$current_time = strtotime(date('Y-m-d')); // gets current time in seconds since epoch
			$min_date = $current_time - 9.46708e8; // 30 years in seconds
			if (strtotime($array[$name]) < $min_date) {
				$errors .= "Date visited must be less than 30 years ago<br>";
			} elseif (strtotime($array[$name]) > $current_time) {
				$errors .= "Date visited cannot be in the future<br>";
			}
		}
	}

	function validateID($name, $array, &$errors) {
		if (!isset($array[$name])) {
			$errors .= "No park ID specified<br>";
		} 		
	}
?>