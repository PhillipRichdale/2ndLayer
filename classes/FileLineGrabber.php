<?php
	//2ndLayer - a simple Linegrabber for files with some CSV specific functionality
	class FileLineGrabber
	{
		public $fieldSeperator = ",";
		public $stringDelimiter = '"';
		
		private $fileFieldNames;
		private $fileFieldNameCount;
		
		private $fileLineArray;
		
		public function __construct($filename)
		{
			$this->fileLineArray = file($filename);
			$this->initFileFieldNames();
		}
		public function getSpecificLineArray($linNum)
		{
			$lineArray = array();
			
			if($thisLine = $this->getSpecificLineInFile($linNum))
			{
				$thisLineArray = explode($this->fieldSeperator, $thisLine);
				for($i = 0; $i < $this->fileFieldNameCount; $i++)
				{
					$lineArray[$this->fileFieldNames[$i]] = trim(str_replace($this->stringDelimiter, '', $thisLineArray[$i]));
				}
			} else {
				$lineArray = false;
			}
			//error_log("\n\n\n".json_encode($lineArray)."\n\n\n");
			return $lineArray;
		}
		public function getSpecificLineInFile($linNum)
		{
			if (array_key_exists($linNum , $this->fileLineArray))
			{
				return $this->fileLineArray[$linNum];
			} else {
				return false;
			}
		}
		
		private function initFileFieldNames()
		{
			$getLine = $this->getSpecificLineInFile(0);
			//error_log("\n\n\n".$getLine."\n\n\n");
			//$fNames = explode($this->fieldSeperator, $getLine);
			$fNames = explode(",", $getLine);
			foreach($fNames as $fName)
			{
				$this->fileFieldNames[] = trim(str_replace($this->stringDelimiter, '', $fName));
			}
			$this->fileFieldNameCount = count($this->fileFieldNames);
		}
	}
?>
