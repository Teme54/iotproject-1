<!DOCTYPE html >
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <title>Google Maps Geolocator 1.1a</title>
    <link rel="stylesheet" type="text/css" href="style.css" >
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

  </head>

<h1 id=header>LocatIot Tracker</h1>

  <body>
    <button type=button id=updateMapBtn onclick="updateMap()">Update Map</button>
    <button type=button id=resetPos onclick="resetPos()">Reset Position</button>

      <form id="query" method="post" action="http://139.59.155.145/XML_SQL.php">

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

        <input id="tsminf" name="tsminf" type="hidden" />
        <input id="tsmaxf" name="tsmaxf" type="hidden" />

        <div id="time-range">
       <p>Time Range: <span id="slidertime" name="slidertime" class="slidertime"></span> - <span id="slidertime2" name="slidertime2" class="slidertime2"></span>
       </p>
       <div class="sliders_step1">
           <div id="slider-range"></div>
       </div>
      </div>

      </form>

    <div id="map"></div>

    <script>

// Määritellään globaalit muuttujat

  var activeInfoWindow; // Infoikkuna, joka on näkyvillä
  var map;              // Karttaobjectin esittely
  var lastPoint;        // Viimeisen piirretyn markkerin paikka
  var idSet = null;     // Markkeriin kohdistamista varten, tarkistetaan onko markkerin ID asetettu vertailukohdaksi

// jQuery funktio, ajetaan kun Submit-painiketta painetaan

var dt_from = "2017/11/02 00:00:00";
var dt_to = "2017/12/29 23:59:00";

$('.slidertime').html(dt_from);
$('.slidertime2').html(dt_to);
var min_val = Date.parse(dt_from)/1000;
var max_val = Date.parse(dt_to)/1000;
var min_slider = "min";
var max_slider = "max";
var tsmin;
var tsmax;


function zeroPad(num, places) {
  var zero = places - num.toString().length + 1;
  return Array(+(zero > 0 && zero)).join("0") + num;
}

function formatDT(dt, slidertype) {
  var year = dt.getFullYear();
  var month = zeroPad(dt.getMonth()+1, 2);
  var date = zeroPad(dt.getDate(), 2);
  var hours = zeroPad(dt.getHours(), 2);
  var minutes = zeroPad(dt.getMinutes(), 2);
  var seconds = zeroPad(dt.getSeconds(), 2);
  if ( slidertype === "min" ){
    tsmin = year + '-' + month + '-' + date + ' ' + hours + ':' + minutes + ':' + seconds;
    console.log("Min: " + tsmin);
    elem_min = document.getElementById("tsminf");
    elem_min.value = tsmin;
  }
  if ( slidertype === "max") {
    tsmax = year + '-' + month + '-' + date + ' ' + hours + ':' + minutes + ':' + seconds;
    console.log("Max: " + tsmax);
    elem_max = document.getElementById("tsmaxf");
    elem_max.value = tsmax;
  }
  return year + '-' + month + '-' + date + ' ' + hours + ':' + minutes + ':' + seconds;
};

$("#slider-range").slider({
  range: true,
  min: min_val,
  max: max_val,
  step: 10,
  values: [min_val, max_val],
  slide: function(e, ui) {
    var dt_cur_from = new Date(ui.values[0]*1000); // format("yyyy-mm-dd hh:ii:ss");
    $('.slidertime').html(formatDT(dt_cur_from, min_slider));
    var dt_cur_to = new Date(ui.values[1]*1000); // format("yyyy-mm-dd hh:ii:ss");
    $('.slidertime2').html(formatDT(dt_cur_to, max_slider));
  }
});

$("#query").submit(function(event) {
          event.preventDefault();
          var $form = $( this ),
          url = $form.attr( 'action' ); // url muuttujaan tallennetaan XML_SQL-url

// Tehdään olio posting, konstruktorille attribuuteiksi POST-metodille muuttujia

          var posting = $.post( url, { idStart: $('#idStart').val(), idEnd: $('#idEnd').val(),
          uname: $('#uname').val(), passwd: $('#passwd').val(), tsminf: $('#tsminf').val(), tsmaxf: $('#tsmaxf').val() } );

// Kun funktion suoritus valmis eli XML on päivitetty, kartta päivitetään

          posting.done(function( data ) {
          console.log("&lumen");
          initMap();

            });
          posting.fail(function(data) {
          console.log("&failin");
          initMap();
          });
          });

// AJAX:lla päivitetään GET-metodilla XML data:ssa lähetetyillä parametreillä

          function updateMap () {
              $.ajax({ url: "http://139.59.155.145/XML_SQL.php",
                  type: "GET",
                  url: 'http://139.59.155.145/XML_SQL.php',
                  data: {uname: 'stduser', passwd: 'samplepass916'},
                  });
            initMap();
            console.log(tsmin);
            console.log(tsmax);
            }


        function initMap() {

// Luodaan karttaolio, keskitetään kartta ja asetetaan zoom sekä kartan tyyppi

        map = new google.maps.Map(document.getElementById('map'), {
          center: new google.maps.LatLng(64.9973158, 25.4867483),
          zoom: 18,
          mapTypeId: 'hybrid'

        });

// idSet nollataan markkerin paikan nollaamiseksi

        idSet = null;

// Ladataan ja luetaan XML-tiedosto

          downloadUrl('http://139.59.155.145/xml/GMaps.xml', function(data) {
            var xml = data.responseXML;
            var markers = xml.documentElement.getElementsByTagName('marker');

// Luodaan taulukko, ja haetaan XML:sta jokaisen elementin attribuutit (ID, ts, lat, lon)

            Array.prototype.forEach.call(markers, function(markerElem) {

              var ID = markerElem.getAttribute('ID');
              var timestamp = markerElem.getAttribute('timestamp');
              var iLatitude = parseFloat(markerElem.getAttribute('latitude'));
              var iLongitude = parseFloat(markerElem.getAttribute('longitude'));

// Luodaan markkeriolio ja sille sijainti (latlng)

              var point = new google.maps.LatLng(iLatitude, iLongitude);

              var marker = new google.maps.Marker({
                map: map,
                position: point,

              });

// Luodaan infoikkuna ja asetetaan siihen sisältö

              marker['infowindow'] = new google.maps.InfoWindow({
                content: "ID: " + ID + "<br>" + "Timestamp: " + timestamp + "<br>" + "Latitude: " + iLatitude + "<br>" + "Longitude: " + iLongitude
              });

// Listener, infoikkuna avataan kun markkeria klikkaa, kartta myös keskitetään markkeriin panTo-funktiolla

              google.maps.event.addListener(marker, 'click', function() {
                activeInfoWindow && activeInfoWindow.close();
                this['infowindow'].open(map, this);
                activeInfoWindow = this['infowindow'];
                map.panTo(marker.getPosition());
              });
// Tarkistetaan, onko kartta keskitetty uusimpaan markkeriin (eli korkeimmalla ID:lla olevaan markkeriin)
// Vertailukohdaksi asetetaan ensimmäinen markkerin ID, ja siihen peilataan seuraavia, jos on isompi ID, korvataan piste sillä

              if ( idSet == null ) {
                idSet = ID;
                lastPoint = point;
              }

              if ( ID > idSet ) {
              lastPoint = point;
              }

              else {}
            });
// Keskitetään kartta

            map.panTo(lastPoint);

          });
      }

// Hakee XML-tiedoston URL:sta (XMLHttpRequest) kun sivun lataaminen on valmis (State 4)

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

// Kartan paikan resetoimista varten, helpottaa Oulun alueella liikkumista kartassa, zoomaa ulos

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
