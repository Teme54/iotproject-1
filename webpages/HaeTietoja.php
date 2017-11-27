
<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// muodostetaan yhteys tietokantaan
try {

  $username = $_GET['usernamelogin'];
  $password = $_GET['passwordlogin'];

if ( $_GET['usernamelogin'] == null || $_GET['passwordlogin'] == null ) {
  $username = "stduser";
  $password = "samplepass916";
}
else {}

$yhteys = new PDO("mysql:host=139.59.155.145;dbname=locatiot", $username, $password);

}
catch (PDOException $e) {
die("ERROR: " . $e->getMessage());
}

echo "Yhteys muodostettu tunnuksella: " . $username ."";
echo "<br>" . "<br>";
echo "ID";
echo "&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp";
echo "Latitude";
echo "&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp";
echo "Longitude";
echo "&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp";
echo "Timestamp";
echo "<br>" . "<br>";

// virheenkäsittely: virheet aiheuttavat poikkeuksen
$yhteys->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// merkistö: käytetään latin1merkistöä;
//toinen yleinen vaihtoehto on utf8.
$yhteys->exec("SET NAMES latin1");
// valmistetaan kysely
$kysely = $yhteys->query("SELECT * FROM locatiot ORDER BY ID DESC")->fetchAll();



foreach($kysely as $results) {

  echo $results['ID'];
  echo "&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp";
  echo $results['latitude'];
  echo "&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp";
  echo $results['longitude'];
  echo "&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp";
  echo $results['timestamp'];

  echo "<br>";
}

?>
