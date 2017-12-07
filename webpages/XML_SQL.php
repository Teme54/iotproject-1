<?php
//Virheilmoitukset näkyville, mikäli sellaisia on

error_reporting(E_ALL);
ini_set('display_errors', 1);
$idS = null;
$idE = null;

try {

// Tarkistetaan, onko POST-metodilla asetettu muuttujien username, password, idStart ja idEnd
// Mikäli on, saadut arvot asetetaan muuttujiin, jos ei, niin asetetaan GET-metdoista saadut arvot

    if (isset($_POST['uname'])) {
        $username = $_POST['uname'];
        echo "Username by POST-method. ";
    } else {
        $username = $_GET['uname'];
        echo "Username by GET-method. ";
    }

    if (isset($_POST['passwd'])) {
        $password = $_POST['passwd'];
        echo "Password by POST-method. ";
    } else {
        $password = $_GET['passwd'];
        echo "Password by GET-method. ";
    }
    if (isset($_POST['tsminf'])) {
        $tsminf = $_POST['tsminf'];
        echo "Timestamp MIN value POSTED, MIN: ";
        echo $tsminf;
        echo " ";
    } else {
        echo "Timestamp MIN value wasn't got";
    }
// tähän kysely select * from locatiot where timestamp between '2017-11-25 00:00:00' and '2017-11-30 16:03:50';

    if (isset($_POST['tsmaxf'])) {
        $tsmaxf = $_POST['tsmaxf'];
        echo "Timestamp MAX value POSTED, MAX: ";
        echo $tsmaxf;
        echo " ";
    } else {
        echo "Timestamp MAX value wasn't got";
    }
// Jos ID Start & ID End muuttujat on annettu GMaps-skriptissä, niin SQL-kyselyä muutetaan hakemaan markkerit tietyltä aikavälitä
// Jos arvot on tyhjät, valitaan vakiona viimeiset 10 markkeria

    if (!empty($_POST['idStart']) && !empty($_POST['idEnd'])) {
        $idS = $_POST['idStart'];
        $idE = $_POST['idEnd'];
        echo "Query: ID Start & ID End set  ";
        $kysely = "SELECT * FROM locatiot WHERE ID >= '$idS' AND ID <= '$idE'";
    }
    else {
      if ( $_POST['tsmaxf'] === null && $_POST['$tsminf'] === null ){
          $kysely = "SELECT * FROM locatiot ORDER BY ID DESC LIMIT 10";
          echo "Query: Default, last 10 markers chosen. ";
        }
        else {
          $kysely= "SELECT * FROM locatiot WHERE timestamp BETWEEN '$tsminf' AND '$tsmaxf'";
          echo "Query: Markers chosen between two timestamp values";
        }
    }

// Uusi PDO-olio, muodostetaan yhteys MySQL-tietokantaan

    $yhteys = new PDO("mysql:host=139.59.155.145;dbname=locatiot", $username, $password);
} catch (PDOException $e) {
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
echo $doc->saveXML();
$doc->save('xml/GMaps.xml');
