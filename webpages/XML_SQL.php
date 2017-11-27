<?php
//Virheilmoitukset näkyville, mikäli sellaisia on

error_reporting(E_ALL);
ini_set('display_errors', 1);


try {

// Username ja pass GET-menetelmällä saadaan URL:sta esim. 11.11.11.11/test.php?username=123&password=asd

if (isset($_POST['uname'])) {
  $username = $_POST['uname'];
}
else {
  echo "Username undefined";
}

if (isset($_POST['passwd'])) {
  $password = $_POST['passwd'];
}
else {
  echo "Password undefined";
}

if (!empty($_POST['idStart']) && !empty($_POST['idEnd'])) {
  $idS = $_POST['idStart'];
  $idE = $_POST['idEnd'];
  $kysely = "SELECT * FROM locatiot WHERE ID >= '$idS' AND ID <= '$idE'";
}
else {
  $kysely = "SELECT * FROM locatiot ORDER BY ID DESC LIMIT 10";
}



$yhteys = new PDO("mysql:host=139.59.155.145;dbname=locatiot", $username, $password);

}
catch (PDOException $e) {
die("ERROR: " . $e->getMessage());
}

// virheenkäsittely: virheet aiheuttavat poikkeuksen
$yhteys->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// merkistö: käytetään latin1merkistöä;
//toinen yleinen vaihtoehto on utf8.
$yhteys->exec("SET NAMES latin1");
// valmistetaan kysely
$stmt = $yhteys->query($kysely);
echo $kysely;
$stmt->setFetchMode(PDO::FETCH_ASSOC);

// Uusi Document object model olio

$doc = new DOMDocument('1.0', 'UTF-8');
$doc->formatOutput = true;
// Oliolle $doc kutsutaan funktiota createElement, joka luo XML-tiedostoon uuden elementin

$markers = $doc->createElement('markers');

// For each käy läpi jokaisen tietueen ja tekee uuden marker-elementin, sekä asettaa jokaiselle elementille attribuutin eli ominaisuuden

foreach ($stmt as $row) {
    $entry = $doc->createElement('marker');
    $entry->setAttribute('ID', $row['ID']);
    $entry->setAttribute('latitude', $row['latitude']);
    $entry->setAttribute('longitude', $row['longitude']);
    $entry->setAttribute('timestamp', $row['timestamp']);

    // Muutokset asetetaan voimaan jokaiseen marker-elementtiin käskyllä appendChild

    $markers->appendChild($entry);

}

// Muutokset asetetaan voimaan markers-elementtiin eli pääelementtiin

$doc->appendChild($markers);

// Set the appropriate content-type header and output the XML
//header('Content-type: text/xml');
header('Content-type: application/xml');

$doc->save('GMapsTest.xml');
echo $doc->saveXML();
