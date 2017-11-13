<?php
//Virheilmoitukset näkyville, mikäli sellaisia on

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {

// Username ja pass GET-menetelmällä saadaan URL:sta esim. 11.11.11.11/test.php?username=123&password=asd

$username = $_GET['usernamelogin'];
$password = $_GET['passwordlogin'];

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
$stmt = $yhteys->query('SELECT * FROM locatiot');
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

$doc->save('GMaps.xml');
echo $doc->saveXML();
