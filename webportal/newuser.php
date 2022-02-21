<?php

if (!isset($_POST['submit'])) {
    header("Location: /");
}

include "functions.php";

$scheduler = new Scheduler(null, false);

$code = $scheduler->new_code();
$name = htmlspecialchars(stripslashes($_POST['name']));
$url = $_POST['url'];

if (empty($name)) {
    echo "Vul een naam in!";
} else if (empty($url)) {
    echo "Vul een URL in";
} else if (!$scheduler->valid_url($url)) {
    echo "Zorg dat de URL geldig is. Bekijk de instructie <a href='info.php'>hier</a>";
} else if (!$scheduler->create_user($code, $name, $url)) {
    echo "Er is een fout opgetreden. Probeer het later opnieuw";
} else {
    echo "Succesvol aangemaakt! Je persoonlijke code is: ".$code;
}

?>