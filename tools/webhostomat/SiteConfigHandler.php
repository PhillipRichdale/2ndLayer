<?php

class SiteConfigHandler
{
	private $templatePath = "/etc/apache2/sites-available/template";
	
	public $validDirsFile = false;
	public $domainString = false;
	public $newConfigFileName = false;
	
	public function __construct(
		$validDirsFile,
		$domainString,
		$newConfigFileName
	){
		$this->validDirsFile = $validDirsFile;
		$this->domainString = $domainString;
		$this->newConfigFileName = $newConfigFileName;
	}
	public function processAndSaveNewConfig()
	{
		$newConfig = file_get_contents($this->templatePath);
		$newConfig = str_replace("###DOMAIN_STRING###", $this->domainString, $newConfig);
		$newConfig = str_replace("###DOCUMENT_ROOT###", $this->docRoot, $newConfig);
		file_put_contents("/etc/apache2/sites-available/".$this->newConfigFileName , $newConfig);
		
		$message = exec("ln -s /etc/apache2/sites-available/".$this->newConfigFileName.
		" /etc/apache2/sites-enabled/".getNextEnabledNumber().$this->newConfigFileName);
	}
	public function listValidDirectories()
	{
		$callback = $this->checkDirPath($path);
		$temp = file($this->validDirsFile);
		$temp = array_filter($temp, $callback);
		return $temp;
	}
	public function restartApache()
	{
		$message = exec("service apache2 restart");
		return "Apache service restartet ($message). Refresh this view after 10 seconds.";
	}
	private function checkDirPath($path)
	{
		return strlen($path) > 2;
	}
	private function getNextEnabledNumber()
	{
		//$enabledRaw = exec("ls -A /etc/apache2/sites-enabled/");
		$enabledArray = scandir("/etc/apache2/sites-enabled/");
		function isVhost($filename){return preg_match("\d\d\d", $filename) == 1;}
		array_filter($enabledArray, 'isVhost');
		rsort($enabledArray);
		$enabledArray[0];
	}
}
