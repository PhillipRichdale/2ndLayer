<?php
/**
	This file tests the MySqlConnector class and all its functions.
//**/
	include '../config.php';
	include "$path/classes/DbConnector.php";
	$dbSqlCon = new DbConnector(
					$dbServerType="mysql",
					$dbHost="localhost",
					$dbPort="8889",
					$dbName="2ndlayer",
					$dbUser="2ndlayer",
					$dbPw="2ndlayerTestPw"
	);