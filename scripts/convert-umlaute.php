<?php
/**
 * This script repairs German special chars in a MySQL DB after a switch from ISO to UTF8
 * Original Scipt courtesy of Boris Bojic (Thanks for the contribution!)
 * 
 * German description:
 * Dieses Skript repariert in einer MySQL Datenbank alle kaputten Umlaute und Ligaturen bei Umstellung von ISO->UTF8
 */

// === [ Content / Charset ] ===================================================
header('Content-Type: text/html; charset=utf-8');

// PHP auch explizit auf UTF-8 setzen
mb_internal_encoding('UTF-8');

$db = array();

$db['host']	= "localhost";
$db['uname']	= "mysql_user";
$db['password']	= "mysql_pass";
$db['database']	= "datenbankname";
	


$dbconnect = mysql_connect($db['host'], $db['uname'], $db['password']) or die ("Couldn't connect to Database./Konnte keine Verbindung zur Datenbank aufnehmen.");
mysql_select_db($db['database'],$dbconnect) or die ("Error while selecting database. / Fehler beim Auswählen der Datenbank.");

mysql_set_charset('utf8');


echo '<pre>';


function getTables($db)
{
	$result = mysql_query("SHOW TABLES FROM " . $db['database']);
	
	while($row = mysql_fetch_row($result)){
		$res[] = $row[0];
	}
	return $res;
}

function getColumns($table)
{
	$table = mysql_real_escape_string($table);

	$mysqlres = mysql_query("SHOW COLUMNS FROM " . $table);
	while($row = mysql_fetch_row($mysqlres)){
		$res[] = $row[0];
	}

	return $res;
}

// Alle Tabellen ermitteln
$tablesArray = getTables($db);
	
// Alle Spalten pro Tabelle ermitteln und durcharbeiten
foreach($tablesArray AS $table)
{	
	$affectedRows = 0;
	$spalten = getColumns($table);

	echo "Tabelle: " . $table . "<br />";

	foreach($spalten AS $spalte)
	{
		echo "...Spalte: " . $spalte . "<br />";
		$query = '
			UPDATE `' . $table . '` SET
			`' . $spalte . '` = REPLACE(`' . $spalte . '`,"ÃŸ", "ß"),
			`' . $spalte . '` = REPLACE(`' . $spalte . '`, "Ã¤", "ä"),
			`' . $spalte . '` = REPLACE(`' . $spalte . '`, "Ã¼", "ü"),
			`' . $spalte . '` = REPLACE(`' . $spalte . '`, "Ã¶", "ö"),
			`' . $spalte . '` = REPLACE(`' . $spalte . '`, "Ã„", "Ä"),
			`' . $spalte . '` = REPLACE(`' . $spalte . '`, "Ãœ", "Ü"),
			`' . $spalte . '` = REPLACE(`' . $spalte . '`, "Ã–", "Ö"),
			`' . $spalte . '` = REPLACE(`' . $spalte . '`, "â‚¬", "€")
		';
	
		mysql_query($query) OR die(mysql_error() . $query);
		$affectedRows += mysql_affected_rows();
	}
	echo "Tabelle " . $table . " aktualisiert, Datensätze: " . $affectedRows . "<br /><br />";
}