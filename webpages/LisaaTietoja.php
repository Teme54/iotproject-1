<?php

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// muodostetaan yhteys tietokantaan
try {
$username = $_GET['usernamelogin'];
$password = $_GET['passwordlogin'];
$ilatitude = $_GET['ilatitude'];
$ilongitude = $_GET['ilongitude'];


$yhteys = new PDO("mysql:host=139.59.155.145;dbname=locatiot", $username, $password);

}
catch (PDOException $e) {
die("ERROR: " . $e->getMessage());
}

echo "Yhteys muodostettu";
echo "<br>" . "<br>";
echo $username;
echo $password;
echo $ilatitude;
echo $ilongitude;

// virheenkäsittely: virheet aiheuttavat poikkeuksen
$yhteys->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// merkistö: käytetään latin1merkistöä;
//toinen yleinen vaihtoehto on utf8.
$yhteys->exec("SET NAMES latin1");
// valmistetaan kysely

$preparequery = "INSERT INTO locatiot VALUES
(NULL, '".$ilatitude."', '".$ilongitude."', NULL)";
$insertquery = $yhteys->query($preparequery);
?>
