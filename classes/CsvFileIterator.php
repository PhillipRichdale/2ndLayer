<?php
	/** 2ndLayer Toolkit - CsvIterator
	 * Diese Klasse öffnet und iteriert über eine CSV Datei (der Trenner ','
	 * ist als Vorgabe eingestellt) und führt für jede enthaltene Zeile eine übergebene
	 * Callbackfunktion mit der Zeile als Parameter aus. String Delimiter,
	 * wie einschließende Anführungsstriche (") werden *nicht* erwartet und
	 * werden vollständig entfernt!
	 * 
	 * Der Parameter ist ein Assoc-Array mit dem Spaltennamen als Schlüssel
	 * und dem jeweils zugehörigen Wert. Die Einträge der ersten Zeile werden
	 * als Spaltennamen interpretiert und als Schlüssel im Assoc Array an
	 * die gewünschte Callbackfunktion übergeben.
	 *
	 * Callbackobjekt und der Name der Callbackfunkion müssen als Parameter
	 * dem Konstruktor übergeben werden. Optional kann eine Verzögerung in
	 * Sekunden als Zahl als dritter Parameter übergeben werden. Der
	 * Standardwert für die Verzögerung ist 12 Sekunden. Wird delay auf
	 * 'false' gesetzt, findet keine Verzögerung statt.
	 * 
	 * Datenfehlerhandhabung
	 * Der Iterator überspringt automatisch Zeilen, deren Feldanzahl nicht
	 * der der ersten Spaltenbezeichnerzeile entspricht und zählt diese als
	 * Datenfehler. Nach Ende der Iteration gibt die Klasse die Anzahl der
	 * iterierten Zeilen und die Anzahl der Datenfehler als Stringmeldung zurück.
	 */
	
	class CsvFileIterator
	{
		private $csvFile;
		
		private $fileFieldNames;
		private $fileFieldNameCount;
		
		private $datasetCount=0;
		
		private $callbackObject = "";
		private $callbackFunction = "";
		private $callback;
		private $delay;
		
		private $seperator;
		
		// mit einem delay von 12 Sekunden schaffen wir 7200 Mails in 24 Stunden,
		// das passt und sollte auch die schwächste Mailumgebung schonen
		public function __construct($callbackObject, $callbackFunction, $seperator=",", $delay=12)
		{
			$this->callbackObject = $callbackObject;
			$this->callbackFunction = $callbackFunction;
			$this->callback = array(&$this->callbackObject, $this->callbackFunction);
			$this->seperator = $seperator;
			$this->delay = $delay;
		}
		public function iterateCsvFile($csvFile)
		{	
			$this->openFile($csvFile);
			
			$count = 0;
			$this->datasetCount = 0;
			
			while($importLine = $this->getImportLine())
			{
				call_user_func($this->callback, $importLine);
				$count++;
				$this->datasetCount++;
				if ($this->delay)
				{
					sleep($this->delay);
				}
			}
			
			$out = "";
			$out .= "\nAnzahl der iterierten CSV Zeilen: ".$this->datasetCount;
			$out .= "\nAnzahl der übersprungenen Zeilen (Datenfehler): ".$this->dataErrorCount."\n";
			return $out;
		}
		private function openFile($filename)
		{
			$this->csvFile = fopen($filename ,"r");
			
			#Erste Zeile sind Feldnamen:
			$getLine = fgets($this->csvFile);
			
			// Es können whitespaces in den Spaltennamen auftauchen
			// - durch unsaubere CSV Dateien oder so,
			// diese müssen entfernt werden:
			$fNames = explode($this->seperator, $getLine);
			foreach($fNames as $fName)
			{
				$this->fileFieldNames[] = trim(str_replace('"', '', $fName));
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
			$getLine = fgets($this->csvFile);
			$rLine = explode($this->seperator, $getLine);
			
			if(count($rLine) != $this->fileFieldNameCount)
			{
				$rLine = $this->getValidFileLine();
				$this->dataErrorCount++;
			}
			return $rLine;
		}
	}
?>
