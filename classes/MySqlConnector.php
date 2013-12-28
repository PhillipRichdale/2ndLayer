<?php
/**
	Developer: actcontpr, Date: 27.12.13, Time: 22:22
	This class connects to a mysql database and provides the utility functions
	required for doing basic analysis and direct processing of the mysql database.

//**/

class MySqlConnector
{
	private $dbConnect;
	private $dbName;

	private $allAttribs = array();

	private $importFile;
	private $targetTable;

	private $fileFieldNames;
	private $fileFieldNameCount;

	private $importCount=0;

	public function __construct()
	{
		//correctly initialise DB:
		$this->execSql("SET NAMES 'utf8'");
		$this->execSql('SET CHARACTER SET UTF8');
		$this->execSql("SET character_set_results = 'utf8', character_set_client = 'utf8',
			character_set_connection = 'utf8', character_set_database = 'utf8',
			character_set_server = 'utf8', character_set_filesystem = 'utf8'");
	}

	public function genModel($tNames = false)
	{
		if (!$tNames)
		{
			$result = $this->execSql("SHOW TABLES");
			$resArray = mysql_fetch_array($result);
			print_r($resArray);
			exit();
		}

	}
	public function importFile2Table($importFile, $targetTable)
	{
		$this->targetTable = $targetTable;
		$this->allAttribs = $this->getAttributeList($targetTable);

		$this->openFile($importFile);

		$count = 0;
		$this->importCount = 0;
		$sql = $this->getInsertSql();

		while($importLine = $this->getImportLine())
		{
			$sql .= "(";
			foreach($importLine as $key => $val)
			{
				$val = str_replace("'", "´", $val);
				$sql .= "'$val', ";
			}

			#Komma, Leerzeichen abschneiden:
			$sql = substr($sql, 0, -2);

			$sql .= "),";
			$count++;
			$this->importCount++;
			if (3 == $count)
			{
				$count = 0;

				#Komma abschneiden:
				$sql = substr($sql, 0, -1);

				#echo "\n\n\n$sql\n\n\n";exit();

				execSql("SET NAMES 'utf8'");
				execSql('SET CHARACTER SET UTF8');
				execSql($sql);
				$sql = $this->getInsertSql();
			}
		}

		#letzte Einfügungen vornehmen:
		if (0 < $count)
		{
			#Komma abschneiden:
			$sql = substr($sql, 0, -1);
			execSql($sql);
		}

		$out = "";
		$out .= "\nAnzahl der importierten Datensätze: ".$this->importCount;
		$out .= "\nAnzahl der nicht importierten Datensätze: ".$this->dataErrorCount."\n";
		return $out;
	}
	private function getInsertSql()
	{
		$sql = " INSERT INTO ".$this->targetTable." (";
		foreach($this->fileFieldNames as $fieldName)
		{
			$sql .= "$fieldName, ";
		}

		#Komma, Leerzeichen abschneiden:
		$sql = substr($sql, 0, -2);

		$sql .= ") VALUES ";
		return $sql;
	}
	private function openFile($filename)
	{
		$this->importFile = fopen($filename ,"r");

		#Erste Zeile sind Feldnamen:
		$getLine = fgets($this->importFile);

		// Es können whitespaces in den Spaltennamen auftauchen
		// (u.a. SplitHausnummerOffStrasse.php kann sowas verursachen).
		// Diese müssen entfernt werden:
		$fNames = explode(";", $getLine);
		foreach($fNames as $fName)
		{
			$this->fileFieldNames[] = trim($fName);
		}

		$this->fileFieldNameCount = count($this->fileFieldNames);

		$this->ursprung = "Datei: $filename";
		$this->importSourceIsFile = true;
	}
	private function getImportLine()
	{
		$lineArray = array();
		$thisLine = $this->getValidFileLine();

		for($i = 0; $i < $this->fileFieldNameCount; $i++)
		{
			$lineArray[$this->fileFieldNames[$i]] = $thisLine[$i];
		}
		return $lineArray;
	}
	private function getValidFileLine()
	{
		$getLine = fgets($this->importFile);
		$rLine = explode(";", $getLine);

		if(count($rLine) != $this->fileFieldNameCount)
		{
			$rLine = $this->getValidFileLine();
			$this->dataErrorCount++;
		}
		return $rLine;
	}
	private function getAttributeList($entity)
	{
		$sql = "SHOW COLUMNS FROM $entity";
		$result = $this->execSql($sql);
		$rM = array();
		while($attrib = mysql_fetch_assoc($result))
		{
			$rM[] = $attrib['Field'];
		}
		return $rM;
	}
	private function execSql($sql)
	{
		mysql_select_db($this->dbName, $this->dbConnect) or die ("Unable to select database ".$this->dbName."\n");
		$result = mysql_query($sql, $this->dbConnect) or die ("Unable to run query on lx13.".$this->dbName."\n");
		return $result;
	}
}
