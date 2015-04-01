<?php
	// 2ndLayer - This is a very small utility script to generate
	// connector classes from DB tables. This also serves as an example
	// how the DbConnector and Generator classes are used. You probably
	// won't need any other script though.

	require 'classes/DbConnector.php';
	require 'classes/Generator.php';
	$db = new DbConnector(
					$dbServerType="mysql",
					$dbHost="127.0.0.1",
					$dbPort="3306",
					$dbName="",
					$dbUser="",
					$dbPw="");
	$gen = new Generator($db->getPdoConnection());
	$gen->genClassesFromDb();