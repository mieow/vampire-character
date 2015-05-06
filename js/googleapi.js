var map;
function vtm_initialize() {

	document.getElementById('feedingmap_status').innerHTML = 'Starting Init';
	var centerlat  = document.getElementById('feedingmap_clatID').value;
	var centerlong = document.getElementById('feedingmap_clongID').value;
	var oMapzoom = new Number (document.getElementById('feedingmap_zoomID').value);
	var mapzoom = Number(oMapzoom);
	var maptype = document.getElementById('feedingmap_typeID').value;
	document.getElementById('feedingmap_status').innerHTML = 'Got map settings:' + centerlat + ',' + centerlong + " " + maptype + " at zoom " + mapzoom;

	var mapOptions = {
		zoom: mapzoom,
		center: new google.maps.LatLng(centerlat, centerlong),
		mapTypeId: google.maps.MapTypeId[maptype]
	};
	document.getElementById('feedingmap_status').innerHTML = 'Setup Map Options:' + centerlat + ',' + centerlong + " '" + maptype + "' at zoom " + mapzoom;
  
	var canvas = document.getElementById('map-canvas');
	map = new google.maps.Map(canvas, mapOptions);
	
	document.getElementById('feedingmap_status').innerHTML = 'Loading Domains';
	vtm_loadDomains(map);
  
	document.getElementById('feedingmap_status').innerHTML = 'Ready';
}


/** @this {google.maps.Polygon} */
function vtm_showDomainInfo(event) {

  // Since this polygon has only one path, we can call getPath()
  // to return the MVCArray of LatLngs.
  var vertices = this.getPath();

  // Replace the info window's content and position.
  infoWindow.setContent('<b>Domain Name</b><br>Description');
  infoWindow.setPosition(event.latLng);

  infoWindow.open(map);
}



function vtm_loadScript() {
	var apikey = document.getElementById('feedingmap_apikeyID').value;
	//alert(apikey);

	var script = document.createElement("script");
	script.type = "text/javascript";
	script.src = "http://maps.googleapis.com/maps/api/js?key=" + apikey + "&sensor=false&callback=vtm_initialize";
	document.body.appendChild(script);
}

window.onload = vtm_loadScript;
google.maps.event.addDomListener(window, 'load', vtm_initialize);
