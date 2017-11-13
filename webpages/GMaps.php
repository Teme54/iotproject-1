<!DOCTYPE html >
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <title>Google Maps Geolocator 1.1a</title>
    <style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
       #map {
         height: 100%;
         width: 100%;
       }
      /* Optional: Makes the sample page fill the window. */
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
    </style>
  </head>

  <body>
    <div id="map"></div>

    <script>

        function initMap() {
        var map = new google.maps.Map(document.getElementById('map'), {
          center: new google.maps.LatLng(64.9973158, 25.4867483),
          zoom: 12
        });

          downloadUrl('http://139.59.155.145/GMaps.xml', function(data) {
            var xml = data.responseXML;
            var markers = xml.documentElement.getElementsByTagName('marker');

            Array.prototype.forEach.call(markers, function(markerElem) {

              var ID = markerElem.getAttribute('ID');
              var timestamp = markerElem.getAttribute('timestamp');

              var point = new google.maps.LatLng(
                  parseFloat(markerElem.getAttribute('latitude')),
                  parseFloat(markerElem.getAttribute('longitude')));

              var marker = new google.maps.Marker({
                map: map,
                position: point,

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
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD3cAdJ4TwzleiMXiwKfDlwNeB2JLUmomY&callback=initMap">
    </script>
  </body>
</html>
