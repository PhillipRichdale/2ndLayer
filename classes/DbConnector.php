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
					$dbPort="8889",
					$dbName="eventcal",
					$dbUser="StomatopodLocal",
					$dbPw="LocalStomatopod")
	{
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
			echo $exc->getTraceAsString();
		}
	}
	public function getPdoConnection()
	{
		return $this->db;
	}
	public function genClassesFromDb()
	{
		$el = $this->getEntityList();
		foreach ($el as $entityName)
		{
			$className = ucfirst($entityName);
			$attribs = $this->getEntityAttributeList($entityName);
			file_put_contents(
						"classes/$className.php",
						$this->makeTypeClass($className, $attribs)
				);
		}
	}
	private function getEntityList()
	{
		$sql = "SHOW TABLES";
		$result = $this->db->query($sql);
		$rM = array();
		while($row = $result->fetch())
		{
			$rM[]=$row[0];
		}
		return $rM;
	}
	private function getEntityAttributeList($entity)
	{
		$sql = "SHOW FULL COLUMNS FROM $entity";
		$result = $this->db->query($sql);
		$rM = array();

		/**
		$attrib Format:
			[Field] => id
			[0] => id
			[Type] => int(11)
			[1] => int(11)
			[Null] => NO
			[2] => NO
			[Key] => PRI
			[3] => PRI
			[Default] => 
			[4] => 
			[Extra] => auto_increment
			[5] => auto_increment
		 */
		while($attrib = $result->fetch())
		{
			$rM[] = $attrib['Field'];
		}
		return $rM;
	}
	public function makeTypeClass($className, $attribs)
	{
		$classDef = "";
		$classDef .= "<?php\n";
		$classDef .= "//2ndLayer autogeneration of classes with DbConnector\n";
		$classDef .= "class $className {\n";
		foreach($attribs as $attrib)
		{
			if ("id" == $attrib)
			{
				$access = "private";
			} else {
				$access = "public";
			}
			$classDef .= "	$access \$$attrib;\n";
		}
		$classDef .= "	public function __construct(){}\n";
		$classDef .= "	public function getId()\n";
		$classDef .= "	{\n";
		$classDef .= "		return \$this->id;\n";
		$classDef .= "	}\n";
		/**
		$classDef .= "	\n";
		$classDef .= "	\n";
		$classDef .= "	\n";
		$classDef .= "	\n";
		$classDef .= "	\n";
		$classDef .= "	\n";
		//**/
		$classDef .= "}\n";
		return $classDef;
	}
}
