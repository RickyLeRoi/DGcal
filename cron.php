<?php
    $servername = "server_remoto";
    $username = "user_name";
    $password = "password_DB";
    $databaseremoto = "DB_remoto";
    $databaselocale = "DB_locale";

   // Connetti
$conn = new mysqli($servername, $username, $password);
if (!$conn) {
    die("Non posso connettermi al server: " . $conn->connect_error);
}

$sql = "CREATE DATABASE IF NOT EXISTS ".$databaselocale or die ('Non posso creare il DB: ' . mysql_error() . "\n");

$link = mysqli_connect($servername, $username, $password, $databaselocale);

$sql = "CREATE TABLE immagini (
id INT NOT NULL AUTO_INCREMENT,
fornitore INT NULL,
articolo VARCHAR(20) NULL,
PRIMARY KEY (`id`)
)";

mysql_query("$sql") or die ("Non riesco a creare la tabella");

mysqli_close($link);

?>
