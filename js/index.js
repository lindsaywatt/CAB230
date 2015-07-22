var ajax = new Ajax();
var map_canvas = $("#map-canvas");
var results_section = $("#search-results");
var loading = $("#loading");
var results_container = $("#search-results-container");

//insert google map
google.maps.event.addDomListener(window, 'load', initialize);

function adjustInfoBar() {
	var mobile = (window.getComputedStyle($("nav")).getPropertyValue('position') != "fixed");
	if (mobile) {
		$("#dooblidoo").className = "";
		$("#search-results").style.paddingTop = 0;
	} else {
		//begin display dooblidoo (fixed information bar)
		var search_top = $("#search-results").getBoundingClientRect().top;
		var nav_height = $("nav").getBoundingClientRect().height;
		//display dooblidoo & fix at bottom of nav bar (green information bar)
		//fix dooblidoo (green information bar) to bottom of nav bar on user scroll down
		if ($('body').scrollTop > search_top + nav_height + 40) {
			if ($("#dooblidoo").className.indexOf(" dooblidoo-fixed") < 0) {
				$("#dooblidoo").className += " dooblidoo-fixed";
				$("#dooblidoo").style.top = nav_height + "px";
				$("#search-results").style.paddingTop = $("#dooblidoo").getBoundingClientRect().height + "px";
			}
		//unfix dooblidoo (green information bar) on user scroll up
		} else if ($('body').scrollTop < search_top + nav_height + 41) {
			$("#dooblidoo").className = $("#dooblidoo").className.replace(" dooblidoo-fixed", "");
			$("#search-results").style.paddingTop = 0;
		}
	}
}

document.onscroll = adjustInfoBar;
window.onresize = adjustInfoBar;

//search parks depending on search type (suburb, name, rating, location)
function searchParks(search_title, query, type) {
	hideStatus("#search-status");
	//begin loading screen transition
	results_section.style.transitionDuration = "0.5s";
	//hide results section
	results_section.style.maxHeight = 0;
	loading.style.display = "block";
	//begin results search
	ajax.get("search.php", {query: query, type: type},
		function (response) {
			if (response.responseText.trim() == "error") {
				noResults();
			} else {
				try {
					json = JSON.parse(response.responseText);
					var title = search_title.format({
						num_results: " - found " + json["num_results"] + ((json["num_results"] == 1) ? " park" : " parkz")
					});
					showResults(title, json["search_results"]);
				} catch (e) {
					console.log(e);
					noResults();
				}
			}
		},
		function (response) {
			noResults();
		}
		//end results search
	);
}
//Use in the event of a error
function noResults() {
	setTimeout(function() {
		//hide map_canvas
		map_canvas.style.display = "none";
		//clear the results_container
		results_container.innerHTML = "";
		//reset markers on google map
		deleteMarkers();			
		//display error message on information bar (dooblidoo) 	
		$("#dooblidoo").innerHTML = "No results found";
		loading.style.display = "none";
		results_section.style.maxHeight = results_section.scrollHeight + "px";
	}, 800);
}

//display search results
function showResults(search_title, search_results) {
	setTimeout(function() {
		map_canvas.style.display = "block";
		results_container.innerHTML = "";
		//reset markers on google map
		deleteMarkers();
		//assign database information to appropriate variables
		for (var i = 0; i < search_results.length; i++) {
			//park latitude
			var lat = search_results[i].lat;
			//park longitude
			var lon = search_results[i].lon;
			//park name
			var name = search_results[i].name + ", " + search_results[i].street;
			//park google map marker information display
			var info_window_html =  '<div class="map-info-window">' +
										'<h1>' + search_results[i].name + '</h1>' +
										'<h3>' + search_results[i].street + '</h3>' +
										'<a href="park.php?id=' + search_results[i].id + '">Click here for details</a>'+
									'</div>';				
			var info_window = new google.maps.InfoWindow({
				content: info_window_html
			});
			//add marker to google map
			addMarker(new google.maps.LatLng(lat, lon), name, info_window);
			results_container.innerHTML += search_results[i].html;
		}
		//assign information for search result boxes to variable 'search_result_boxes'
		var search_result_boxes = document.querySelectorAll(".park-result");
		for (var i=0; i<search_result_boxes.length; ++i) {
			//go to the park details page when user clicks a search_result_box
			search_result_boxes[i].onclick = function() {
				gotoDetailsPage(this);
			};
		}
		//fit all markers in the user view of the map
		map.fitBounds(bounds);
		//assign the search information to the dooblidoo (search bar)
		$("#dooblidoo").innerHTML = search_title;
		results_section.style.transitionDuration = "2s";
		loading.style.display = "none";
		results_section.style.maxHeight = (results_section.scrollHeight + (165 * search_results.length)) + "px";
	}, 800);
}

window.onload = function() {
	searchParks("Showing 12 highest rated parkz", "12", "top-parkz");
}

// when the user clicks the "near me" button
// search by user location (within 5kms)
$("#location-button").onclick = function(e) {
	e.preventDefault();
	//search by location
	getLocation(function(pos) {
			searchParks('Showing parkz near you{num_results} within 1km', pos.coords.latitude + ',' + pos.coords.longitude, "location")
		},
		function() {
			alert("couldn't get location");
		}
	);
};


// when the user types in the suburb datalist
// search parks by suburb
$("#suburb-input").oninput = function (e) {
	e.preventDefault();
	var suburb = e.srcElement.value;
	//search by suburb on user selection in datalist
	if (valid_suburbs.indexOf(e.srcElement.value) >= 0) {
		searchParks('Showing parkz in ' + suburb + '{num_results}', suburb, "suburb");
	} else {
		//entire suburb has not been entered into suburb box
		setStatus("#search-status", "<h3>please select a suburb from the list</h3>", "error");
	}
};

//search by name
//when user presses 'enter' in the text box
$("#name-search-button").onclick = function(e) {
	e.preventDefault();
	var name = $("#name-search-field").value.trim();
	//do if name not blank
	if (name.length > 0) {
		searchParks('Showing parkz named ' + name + '{num_results}', name, "name");
	} else {
		setStatus("#search-status", "<h3>type a name into the field</h3>", "error");
	}
};

// fill "valid_suburbs" array with data from the appropriate <datalist>
var valid_suburbs;
var v = $("#valid-suburbs").children;
valid_suburbs = Array(v.length);
for (var i=0; i<valid_suburbs.length; ++i) {
	valid_suburbs[i] = v[i].innerText;
}
//search by rating
var rating = new RatingStars($("#rating-stars"));
rating.setOnClickCallback(function(n) {
	searchParks("Showing parkz with an average rating of at least " + n + " stars{num_results}", n, "rating");
});