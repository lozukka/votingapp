<?php
// Käynnistetään sessio
session_start();

// Tyhjennetään sessiomuuttujat
$_SESSION = array();

// Lopetetaan sessio
session_destroy();

// Ohjataan käyttäjä takaisin kirjautumissivulle
header("Location: login.php");
exit();
?>
