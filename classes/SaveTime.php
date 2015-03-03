<?php
	/** 2ndLayer Toolkit - SaveTime
	 * These functions save the time when they've run into a file.
	 * This is a utility class for testing wether a specific portion
	 * of code or a PHP script has run at a certain time
	 **/

	class SaveTime()
	{
		public function __contruct(){}
		public function saveRunDate()
		{
			# Writes the date of the runtime of this function into the
			# directory of the file that called this function with
			# the Filenameformat >[filename]_lastrun.dat<
			$dateiName = substr(basename(__file__),0,-4)."_lastrunDate.dat";
			$datei = fopen($dateiName, 'w') or die("can't open file");
			fwrite($datei, date("Y-m-d"));
			fclose($datei);
		}
		public function saveRunDay()
		{
			# Writes the Unixday into the Directory of the calling File
			# as a File with the filenameformat >[filename]_lastrunDay.dat<
			# Verzeichnis der aufrufenden Datei als Datei
			# mit dem Dateinnamenformat >[filename]_lastrunUnixDay.dat<
			$dateiName = substr(basename(__file__),0,-4)."_lastrunUnixDay.dat";
			$datei = fopen($dateiName, 'w') or die("can't open file");
			$date = new DateTime();
			fwrite($datei, (string) floor($date->getTimestamp() / (3600 * 24)));
			fclose($datei);
		}
		public function daysSinceLastRun()
		{
			# Zeigt die Zeit seit dem letzten Lauf des Skripts in
			# Tagen an (liesst das gespeicherte Datum in [filename]_lastrun.dat)
			$hits = array();
			$dateiName = substr(basename(__file__),0,-4)."_lastrunUnixDay.dat";
			$datei = fopen($dateiName, 'r');
			$contents = fread($datei, filesize($dateiName));
			//print_r($contents);
			$date = new DateTime();
			return floor($date->getTimestamp() / (3600 * 24)) - (int) $contents;
		}
		public function saveRunHour()
		{
			# Schreibt die Unixstunde in das
			# Verzeichnis der aufrufenden Datei als Datei
			# mit dem Dateinnamenformat >[filename]_lastrun.dat<
			$dateiName = substr(basename(__file__),0,-4)."_lastrun.dat";
			$datei = fopen($dateiName, 'w') or die("can't open file");
			$date = new DateTime();
			fwrite($datei, (string) floor($date->getTimestamp() / 3600));
			fclose($datei);
		}
	}
?>
