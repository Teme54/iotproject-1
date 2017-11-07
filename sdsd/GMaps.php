

<!DOCTYPE html>
<html>
<body>

<h1>Google Maps Geolocator v1.0</h1>

<!--Creates a HTML division for the Google Map -->

<div id="googleMap" style="width:50%;height:400px;"></div>

<!-- Create a function to set the maps properties -->

<script>

function myMap() {

var LatLng = {lat:64.9973939,lng: 25.4868044 };

var mapProp= {
    center:LatLng,
    zoom:17,
};

var map=new google.maps.Map(document.getElementById("googleMap"),mapProp);

var marker = new google.maps.Marker({
	position:LatLng,
	map:map,
	animation:google.maps.Animation.DROP,
	title: 'Hello team'
	});
	marker.addListener('Click', toggleBounce);
}

function toggleBounce() {
	if (marker.getAnimation() !== null ) {
		marker.setAnimation(null);
	}
	else {
		marker.setAnimation(google.maps.Animation.BOUNCE);
	}
}

</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD3cAdJ4TwzleiMXiwKfDlwNeB2JLUmomY&callback=myMap"></script>

</body>
</html>
