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
       #resetPos {
         height: 50px;
         width: 100px;
         z-index: 100;
         position: absolute;
         left: 100px;
         top: 200px;
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
<h1 id=header>LocatIot Tracker</h1>
  <body>
    <button type=button id=updateMapBtn onclick="initMap()">Update Map</button>
    <button type=button id=resetPos onclick="resetPos()">Reset Position</button>


    <form id="query" method="post" action="http://139.59.155.145/XML_SQL_TEST.php">

      <label>ID start</label>
      <input id="idStart" type="text" name="idStart" autocomplete="off"/>

      <label>ID end</label>
      <input id="idEnd" type="text" name="idEnd" autocomplete="off"/>

      <label>MySQL Username</label>
      <input id="uname" type="text" name="uname" autocomplete="off"/>

      <label>MySQL Password</label>
      <input id="passwd" type="password" name="passwd" autocomplete="off"/>

      <input id="submitbtn" type="submit" class="button" name="submit" value="Submit">

      <div class="errorMsg">
        <?php echo $errorMsgLogin ?>
      </div>

    </form>

    <div id="map"></div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

    <script>

        var activeInfoWindow;
        var map;
        var lastPoint;
        var gMarkers = [];
        var idSet = null;

          $("#query").submit(function(event) {
          event.preventDefault();
          var $form = $( this ),
          url = $form.attr( 'action' );
          var posting = $.post( url, { idStart: $('#idStart').val(), idEnd: $('#idEnd').val(),
          uname: $('#uname').val(), passwd: $('#passwd').val() } );
          posting.done(function( data ) {
          alert('success');
            });
          });

        function initMap() {

        map = new google.maps.Map(document.getElementById('map'), {
          center: new google.maps.LatLng(64.9973158, 25.4867483),
          zoom: 20,
          mapTypeId: 'hybrid'

        });

        idSet = null;

          downloadUrl('http://139.59.155.145/GMapsTest.xml', function(data) {
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

              if ( idSet == null ) {
                idSet = ID;
                lastPoint = point;
              }

              if ( ID > idSet ) {
              lastPoint = point;
              }

              else {}
            });

            map.panTo(lastPoint);

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

      function resetPos() {
        map.panTo({lat: 64.9973158, lng: 25.4867483 });
        map.setZoom(12);
      }

    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD3cAdJ4TwzleiMXiwKfDlwNeB2JLUmomY&callback=initMap">
    </script>
  </body>
</html>
