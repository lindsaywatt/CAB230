/* Ajax helper class:
 * Essentially acts as a wrapper for the standard XMLHttpRequest object.
 * Provides 2 public helper functions, get and post.
 * Created by Lindsay Watt for CAB230
*/
function Ajax() {
	/* start private functions */

	// send function will create an instance of XMLHttpRequest dependant on the browser.
	function send(url, success, error, method, data) {
		var xhr = (window.XMLHttpRequest) ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
		xhr.open(method, url, true); // true makes the request asyncronous
		if (method === 'POST') {
			// send the correct header for HTTP POST data
			xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		}
		// set callback functions, they are run when the request has reached it's DONE state (4)
		// run success function if no error (HTTP 200) and error function if there was an error
		xhr.onreadystatechange = function() {
			if (xhr.readyState === 4) {
				if (xhr.status === 200) {
					if (success !== null) { success(xhr); }
				} else {
					if (error !== null) { error(xhr); }
				}
			}
		};
		xhr.send(data);
	}

	// takes an assosiative array and returns a query string
	// eg ['key' : value, 'name' : 'Lindsay'] => 'key=value&name=Lindsay'
	function urlEncodedQueryString(data) {
		var query = [];
		for (var key in data) {
			if (data.hasOwnProperty(key)) {
				query.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
			}
		}
		return query.join('&');
	}

	/* start public functions */
	this.get = function(url, data, success, error) {
		send(url + '?' + urlEncodedQueryString(data), success, error, 'GET', null);
	};
	this.post = function(url, data, success, error) {
		send(url, success, error, 'POST', urlEncodedQueryString(data));
	};
}

/* start form validation class */

// FormValidation takes the form element as it's only parameter and provides full validation
// of that form asuming that the form has been setup correctly.
// Each form input that requires validation must have a data-validation attribute.
// data-validation attribute can contain any number of the below properties, sepparated by a single space
//
//     req
//     len:<min>-<max>
//     regex:<pattern_name>
//     match:<name_of_input_to_match>
//     radio:<name_of_radio_button_group>
//
// eg. <input name='username' type='text' data-validation='req len:5-16 regex:letters'>
//
//     This input will not appear valid until the field contains characters (req),
//     satisfies 5 < length < 16 (len:5-16), and matches the letters regex (regex:letters).
//
// Each input with a data-validation attribute also needs a matching <span> located somewhere
// near the input. The span must have an id of <input_name> + "-status"
//     eg. id='username-status' for the input shown above
// This span will display an error to the user. The span will gain a class of 'status-error'
// when there is an error message and a class of 'status-success' if the input is valid. These styles can be defined in
// CSS, perhaps red for an error and green for a success.
//
// FormValidation automatically finds the submit button and sets its 'disabled' property true unless all fields are valid.
// 
// Created by Lindsay Watt for CAB230

function FormValidation(form) {
	var regex_presets = {
		letters: /^[a-zA-Z]*$/, // letters only
		name: /^[a-zA-Z \-']*$/, // letters, spaces, - and '
		username: /^[a-zA-Z0-9_\.!?-]*$/, // letters, numbers, _ . ! ? and -
		numbers: /^[0-9]*$/, // numbers only
		phone: /^[0-9 \-+]*$/, // numbers, spaces, - and +
		date: /^\d{4}-\d{2}-\d{2}$/, // standard unix date format: YYYY-mm-dd
		email: /[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/
		// email regex was taken from this resource: http://www.regular-expressions.info/email.html
		// it is a simplified implementation of the RFC 5322 syntax standard
	};

	var submit_button = form.querySelector('[type="submit"]'); // find the submit button
	var validation_fields = form.querySelectorAll('[data-validation]'); // find all the fields that need validating
	var fields = []; // this will hold an array of 'field objects' containing infomation about each field
	var VALID = "good"; // constant used to identify if a field is valid or not

	for (var i=0; i<validation_fields.length; ++i) {
		var field = validation_fields[i];
		// construct an annonymous object for each field
		var field_object = {
			field: field,
			status_id: "#" + field.name + "-status",
			validation: field.attributes['data-validation'].value.split(" ")
			// validation is an array of each validation type specified for the field
		};
		// onchange event is required for radio and checkbox fields
		// whereas text based fields require oninput
		if (field.type == "checkbox" || field.type == "radio") {
			field.onchange = validate;
		} else {
			field.oninput = validate;		
		}
		fields.push(field_object); // append the object to the array
	}

	// When the page loads, run an initial validation
	// this will show the user which fields are required and which are not
	window.onload = function() {
		validate();
	};

	// validates all inputs in the field array
	function validate() {
		// assume that the form is valid initially
		var form_valid = true;
		// validate each field using each type of validation specified for that field
		for (var i=0; i<fields.length; ++i) {
			var field = fields[i];
			for (var ii = 0; ii < field.validation.length; ++ii) {
				var status = doValidation(field.validation[ii], field);
				if (status == VALID) {
					// set the appropriate 'status' element to show the 'Good' message
					setStatus(field.status_id, "Good", "success");
					field.field.className = "";
				} else {
					// set the appropriate 'status' element to show the error message
					setStatus(field.status_id, status, "error");
					// add the error class to the input, sets the border red
					field.field.className = "error";
					// the form is no longer valid since one field returned an error
					form_valid = false;
					// break on the first error found for this field, and move to the next one
					break;
				}
			}
		}
		// set the disabled attribute of the button
		submit_button.disabled = !form_valid;
	}

	// takes a single validation command and the field object
	// returns the VALID flag if valid or an error message if not
	function doValidation(validation_string, field_ob) {
		// split the validation command so the parameters can be indexed
		var type = validation_string.split(":");
		// get the value of the field
		var string = field_ob.field.value.trim();

		// case structure runs approptiate actions depending on the validation command string
		if (type[0] == "len") {
			// min and max are indexed from the command string
			var min = parseInt(type[1].split("-")[0]);
			var max = parseInt(type[1].split("-")[1]);
			if (string.length < min) {
				return "Must be longer than " + (min - 1) + " characters";
			} else if (string.length > max) {
				return "Must be shorter than " + (max + 1) + " characters";
			}
		} else if (type[0] == "regex") {
			// get the regex pattern from the preset regex array
			var preset = regex_presets[type[1]];
			if (!preset.test(string)) {
				if (type[1] == "email") {
					// for email regex, provide a more helpful error message
					return "Not a valid email address";
				}
				// if the string does not match regex, must contain invalid characters
				return "Contains invalid characters";
			}
		} else if (type[0] == "match") {
			// used for matching password and confirm password mainly
			// finds the value of the matching field in the form and compares them
			if (string != form.querySelector("[name='" + type[1] + "']").value) {
				return "Does not match";
			}
		} else if (type[0] == "req") {
			// use this for all required fields
			if (string.length === 0) {
				return "Required";
			}
		} else if (type[0] == "radio") {
			// confirms that only one radio button is selected per group
			var radios = form.querySelectorAll("[name='" + type[1] + "']");
			var num_checked = 0;
			for (var i = 0; i < radios.length; ++i) {
				if (radios[i].checked) {
					++num_checked;
				}
			}
			if (num_checked != 1) {
				return "Required";
			}
		}
		// if string has passed all validation, return the VALID flag
		return VALID;
	}
}
/* end form validation class */

/* start rating stars class */

// takes a reference to a container with any number of 'font awesome' stars as children
// sets onclick and on hover listeners so that stars highlight when hovered
// but do not stay highlighted unless clicked
// 
// stars container should look something like this:
// ascending id number is essential
//
// <div id="rating-stars">
//     <i class="fa fa-star-o" id="1"></i>
// 	   <i class="fa fa-star-o" id="2"></i>
// 	   <i class="fa fa-star-o" id="3"></i>
// 	   <i class="fa fa-star-o" id="4"></i>
// 	   <i class="fa fa-star-o" id="5"></i>
// </div>
//
// public function getRating can be used to get the number of selected stars

function RatingStars(stars_container) {
	var num_stars_selected = 0;
	var stars = stars_container.children;
	var callback = null;

	// when the user leaves the star's container
	stars_container.onmouseleave = function() {
		clearStars();
	};

	// set hover and click listeners for each star
	for (var i=0; i<stars.length; ++i) {
		// when a star is hovered on, temporarily highlight stars up to and including that one
		stars[i].onmouseover = function() {
			setStars(parseInt(this.id));
		};
		// when a star is clicked on, permenantly highlight stars up to and including that one
		// and store the number of the currently selected star
		stars[i].onclick = function() {
			var n = parseInt(this.id);
			num_stars_selected = n;
			setStars(n);
			// if a callback is set, run it with the number of stars selected as the argument
			if (callback !== null) {
				callback(num_stars_selected);
			}
		};
	}	

	// unfill stars back to the last clicked one
	function clearStars() {
		for (var s=num_stars_selected; s<stars.length; ++s) {
			stars[s].className = "fa fa-star-o";
		}
	}

	// fill in stars up to the hovered/clicked one
	function setStars(n) {
		clearStars();
		for (var s=num_stars_selected; s<n; ++s) {
			stars[s].className = "fa fa-star";
		}
	}

	this.setOnClickCallback = function(callback_function) {
		callback = callback_function;
	};

	this.getRating = function() {
		return num_stars_selected;
	};
}
/* end rating stars class */

// helper function that provides standard name parameter string formatting
// eg "Hello {name}".format({'name' : "Lindsay"})
function format(str, col) {
	col = typeof col === 'object' ? col : Array.prototype.slice.call(arguments, 1);
	return str.replace(/\{\{|\}\}|\{(\w+)\}/g, function (m, n) {
		if (m == "{{") { return "{"; }
		if (m == "}}") { return "}"; }
		return col[n];
	});
}
String.prototype.format = function (col) {return format(this,col);};

// selector functions to simplify code (similar to jQuery)
function $(query) {
	return document.querySelector(query);
}
function $$(query) {
	return document.querySelectorAll(query);
}

// will get user location and run the callback function
function getLocation(success_callback, error_callback) {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(success_callback);
    } else {
    	error_callback();
    }
}

// clears text from a status element, and hides
function hideStatus(status_id) {
	var s = $(status_id);
	s.innerHTML = "";
	s.style.visibility = "hidden";
}

// adds text to a status element and display it with either success or error classes
function setStatus(status_id, text, type) {
	var s = $(status_id);
	s.innerHTML = text;
	s.className = s.className.replace(" status-success", "");
	s.className = s.className.replace(" status-error", "");
	s.className += " status-" + type;
	s.style.visibility = "visible";
}

// centers the login popup window in the middle of the screen
function alignLoginPopup() {
	var popup_rect = $("#login-popup").getBoundingClientRect();
	var top_offset = (window.innerHeight/2) - (popup_rect.height/2);
	var right_offset = (window.innerWidth/2) - (popup_rect.width/2);
	$("#login-popup").style.top = top_offset + "px";
	$("#login-popup").style.right = right_offset + "px";	
}

// when the login form is submitted
$("#login-form").onsubmit = function(e) {
	e.preventDefault();
	var ajax = new Ajax();
	if (this.attributes.action.value === "login.php") {
		// run this if no user is logged in
		var username = $("#login-username").value;
		var password = $("#login-password").value;
		ajax.post(this.action, {username: username, password: password},
			function (response) {
				// success function
				if (response.responseText !== "error") {
					// if the login was a success then reload the page
					// such that private content is now visible
					location.reload();
				} else {
					setStatus("#login-status", "Invalid username or password", "error");
				}
			},
			function (response) {
				// error function
				console.log("error logging in");
			}
		);
	} else {
		// run this when the user is logged in
		ajax.post(this.action, null,
			function (response) {
				// success function
				if (response.responseText !== "error") {
					// if the logout was a success then reload the page
					// such that private content is hidden now
					location.reload();
				} else {
					setStatus("#login-status", "Couldn't log out...", "error");
				}
			},
			function (response) {
				// error function
				console.log("error logging out");
			}
		);
	}
}

// find all the buttons that open the login window
var login_buttons = $$("[data-login='login-button']");
for (var i = 0; i < login_buttons.length; i++) {
	// set their onclick function to center the login window
	// and show the login popup
	login_buttons[i].onclick = function(e) {
		e.preventDefault();
		alignLoginPopup();
		$("#login-popup").className = "show";
	};
}

// sets an onclick function that will close the login popup
// when the close button is clicked
$("#popup-close").onclick = function() {
	$("#login-popup").className = "hide";
};

// sets an onclick function that will close the login popup
// when anything outside the login button is clicked
$("#window-blanket").onclick = function() {
	$("#login-popup").className = "hide";
};

// variables required for the map
var map;
var bounds;
var markers = [];
var info_windows = [];

function initialize() {
	// center map over brisbane by default
	var default_center = new google.maps.LatLng(-27.4667, 153.0333);
	var map_options = {
		zoom: 14,
		center: default_center,
		scrollwheel: false
	};
	// create the map in the element with the id of map-canvas
	map = new google.maps.Map(document.getElementById('map-canvas'), map_options);
	bounds = new google.maps.LatLngBounds();
}

// Add a marker to the map
function addMarker(location, name, info_window) {
	var marker = new google.maps.Marker({
		position: location,
		animation: google.maps.Animation.DROP,
		title: name,
		map: map
	});
	// when marker is clicked, close other info windows and display the clicked one
	google.maps.event.addListener(marker, 'click', function() {
		closeInfoWindows();
		info_window.open(map, marker);
	});
	bounds.extend(marker.position);
	markers.push(marker);
	info_windows.push(info_window);
}

// Close all info windows
function closeInfoWindows() {
	for (var i = 0; i < info_windows.length; i++) {
		info_windows[i].close();
	}
}

// Deletes all markers
function deleteMarkers() {
	for (var i = 0; i < markers.length; i++) {
		markers[i].setMap(null);
	}
	markers = [];
	bounds = new google.maps.LatLngBounds();
}

// Provide form validation for login form
FormValidation($("#login-form"));