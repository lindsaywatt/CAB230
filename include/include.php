<?php

	//check if attribute $name in array $array not empty
	function exists($name, $array) {
		return (isset($array[$name]) && !empty($array[$name]));
	}

	//check if a text field $name has a value
	function prefill($name) {
		echo (exists($name, $_POST) ? htmlspecialchars($_POST[$name]) : '');
	}

	//check if radio button has a value
	function prefillRadio($name, $value) {
		if (exists($name, $_POST)) {
			echo ($_POST[$name] == $value) ? "checked" : "";
		}
	}

	//check if rating has a value
	function prefillRating($name) {
		if (isset($_POST[$name])) {
			$rating = intval($_POST[$name]);
			return ($rating > 0 && $rating <= 5) ? $rating : 0;
		}
	}

	//display stars based on user rating information
	function make_rating_stars($n) {
		$s = "";
		for ($i=1; $i <= 5; $i++) { 
			if ($i <= $n) {
				//display full stars
				$s .= "<i class='fa fa-star' id='$i'></i>";
			} else {
				//display empty stars
				$s .= "<i class='fa fa-star-o' id='$i'></i>";
			}
		}
		return $s;
	}

	//return images to randomly assign to parks in search results
	function get_random_image() {
		return "img/" . rand(1,8) . ".jpg";
	}

	//sort search results by a specific value in an array. 
	//Used in park search results for location
	function shellSort($arr, $key) {
		$inc = round(count($arr) / 2);
		while($inc > 0) {
			for($i = $inc; $i < count($arr); ++$i) {
				$temp = $arr[$i];
				$j = $i;
				while($j >= $inc && $arr[$j - $inc][$key] > $temp[$key]) {
					$arr[$j] = $arr[$j - $inc];
					$j -= $inc;
				}
				$arr[$j] = $temp;
			}
			$inc = round($inc / 2.2);
		}
		return $arr;
	}
?>