<?php
//credenziali
$host = "server_remoto";
$username = "user_name";
$password = "password_DB";
$databaseremoto = "DB_remoto";

// connetto all'host
$connh = mysql_connect($host, $username, $password); or die ("Non riesco a connettermi: " . mysql_error() . "\n");

//seleziono db_locale se già esistente
$db_selez = mysql_select_db("db_locale", $connh);
if (!db_selez) {
    // crea db_locale dato che non esiste
    $sql = "CREATE DATABASE db_locale" or die ('Non posso creare il DB: ' . mysql_error() . "\n");
    if (mysql_query($sql, $connh)) {
        echo "Database db_locale creato con successo\n";
    } }

// connetto al db
$conndb = mysqli_connect($host, $username, $password, 'db_locale');

//creo tabella immagini
mysql_query ("CREATE TABLE immagini (
id INT NOT NULL AUTO_INCREMENT,
fornitore INT NULL,
articolo VARCHAR(20) NULL,
linkfotoonline VARCHAR(100) NOT NULL,
PRIMARY KEY (`id`)
)") or die ("Non riesco a creare la tabella");

/*
//leggere dati ultima riga da tabella immagini
$dati = mysql_query("select * from immagini");
$array = mysql_fetch_array($dati);
*/

/*
//leggere dati da tabella immagini (riga scelta da WHERE)
$dati = mysql_query("select * from immagini WHERE fornitore="19003" ");
$array = mysql_fetch_array($dati);
*/

/*
//leggere tutti dati da tabella immagini
$dati = mysql_query("select * from immagini");
while ($array = mysql_fetch_array($dati)) {
print "colonna id: $array[id] ";
print "colonna fornitore: $array[fornitore] ";
print "colonna articolo: $array[articolo] ";
pront "colonna link foto: $array[linkfotoonline] ";
}
*/

/*
//modifica dati tabella immagini
$dati = mysql_query("UPDATE immagini SET linkfotoonline="http://imgurl.com/stigranfighi.jpg" WHERE articolo="ipantaloni2016" ");
*/

/*
//inserisci dati in tabella immagini
mysql_query("INSERT INTO immagini (id, fornitore, articolo, linkfotoonline) values ('1','19003','ipantaloni2016','http://imgurl.com/stigranfighi.jpg)");
*/

/*
//cancellare dati dalla tabella immagini (senza WHERE cancella tutto!)
$dati = mysql_query("DELETE FROM immagini WHERE id="5" ");
*/

/*
//numero righe interessate dall'istruzione SQL
$dati = mysql_query("SELECT * FROM immagini");
$num_righe = mysql_num_rows($dati);
*/

/*
//eliminare DB
mysql_drop_db("db_locale");
*/

/*
//lista tabelle
mysql_list_tables("db_locale");
*/

//mysql_free_result($dati);
mysql_close();

?>
