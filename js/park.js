//display google maps
var park_location = new google.maps.LatLng(lat, lon);
google.maps.event.addDomListener(window, 'load', initialize);

//configure google maps marker info window
setTimeout(function() {
	var info_window_html =  '<div class="map-info-window">' +
								'<h1>' + name + '</h1>' +
								'<h3>' + street + '</h3>' +
							'</div>';
	var info_window = new google.maps.InfoWindow({
		content: info_window_html
	});
	//add map marker to google maps
	addMarker(park_location, name + ", " + street, info_window);
	map.setCenter(park_location);
	map.setZoom(16);
}, 1000);

//load park information page halfway down the google maps map
var rect = $("#map-canvas").getBoundingClientRect();
var height = (rect.bottom + rect.top) / 2;
setTimeout(function(){window.scrollBy(0, height);}, 10);