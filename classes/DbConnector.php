<?php
/**
 * Description of DbConnector
 *
 * @author actcontpr
 */

class DbConnector {
	private $dbServerType;
	private $dbHost;
	private $dbPort;
	private $dbName;
	private $dbUser;
	private $dbPw;
	private $db;
	public function __construct(
					$dbServerType="mysql",
					$dbHost="localhost",
					$dbPort="",
					$dbName="",
					$dbUser="",
					$dbPw=""
	){
		$this->dbServerType = $dbServerType;
		$this->dbHost=$dbHost;
		$this->dbPort=$dbPort;
		$this->dbName=$dbName;
		$this->dbUser = $dbUser;
		$this->dbPw = $dbPw;

		try {
			$this->db = new PDO(
				$this->dbServerType.
					":host=".$this->dbHost.
					";port=".$this->dbPort.
					";dbname=".$this->dbName,
				$this->dbUser,
				$this->dbPw
			);
		} catch (Exception $exc) {
			echo "fail";
			echo $exc->getTraceAsString();
		}
	}
	public function getPdoConnection()
	{
		return $this->db;
	}
	public function errorModeOn()
	{
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	public function errorModeOff()
	{
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
	}
}
