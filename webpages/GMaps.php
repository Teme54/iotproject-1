<!DOCTYPE html >
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <title>Google Maps Geolocator 1.1a</title>

    <style>

       #map {
         position: absolute;
         display: block;
         margin: auto;
         height: 100%;
         width: 100%;

       }
       #updateMapBtn {
         height: 50px;
         width: 100px;
         z-index: 100;
         position: absolute;
         left: 100px;
         top: 150px;
         background-color: white;
         font-weight: bold;
       }
      html, body {
        height: 100%;

        margin: 0;
        padding: 0;
      }

      #header {
        z-index: 100;
        position: absolute;
        color: black;
        font-size: 40px;
        font-weight: bold;
        left: 100px;
        top: 50px;
        border-style: solid;
        padding: 8px 8px 8px 8px;
        background-color: white;
      }

    </style>

  </head>
<h1 id=header>LocatIot Tracker v. 0.1a (EARLY ACCESS BUILD)</h1>
  <body>
    <button type=button id=updateMapBtn onclick="updateMap()">Update Map</button>

    <div id="map"></div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

    <script>

        var activeInfoWindow;


        function updateMap () {
          $.ajax({ url: "http://139.59.155.145/XML_SQL.php",
              type: "GET",
              url: 'http://139.59.155.145/XML_SQL.php',
              data: {usernamelogin: 'stduser', passwordlogin: 'samplepass916'},
              });
        initMap();
        }

        function initMap() {

        var map = new google.maps.Map(document.getElementById('map'), {
          center: new google.maps.LatLng(64.9973158, 25.4867483),
          zoom: 12,
          mapTypeId: 'hybrid'

        });

          downloadUrl('http://139.59.155.145/GMaps.xml', function(data) {
            var xml = data.responseXML;
            var markers = xml.documentElement.getElementsByTagName('marker');

            Array.prototype.forEach.call(markers, function(markerElem) {

              var ID = markerElem.getAttribute('ID');
              var timestamp = markerElem.getAttribute('timestamp');
              var iLatitude = parseFloat(markerElem.getAttribute('latitude'));
              var iLongitude = parseFloat(markerElem.getAttribute('longitude'));

              var point = new google.maps.LatLng(iLatitude, iLongitude);

              var marker = new google.maps.Marker({
                map: map,
                position: point,

              });

              marker['infowindow'] = new google.maps.InfoWindow({
                content: "ID: " + ID + "<br>" + "Timestamp: " + timestamp + "<br>" + "Latitude: " + iLatitude + "<br>" + "Longitude: " + iLongitude
              });

              google.maps.event.addListener(marker, 'click', function() {
                activeInfoWindow && activeInfoWindow.close();
                this['infowindow'].open(map, this);
                activeInfoWindow = this['infowindow'];
                map.panTo(marker.getPosition());
              });

            });
        
          });
      }

      function downloadUrl(url, callback) {
        var request = window.ActiveXObject ?
            new ActiveXObject('Microsoft.XMLHTTP') :
            new XMLHttpRequest;

        request.onreadystatechange = function() {
          if (request.readyState == 4) {
            request.onreadystatechange = doNothing;
            callback(request, request.status);
          }
        };

        request.open('GET', url, true);
        request.send(null);
      }

      function doNothing() {}
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD3cAdJ4TwzleiMXiwKfDlwNeB2JLUmomY&callback=updateMap">
    </script>
  </body>
</html>
