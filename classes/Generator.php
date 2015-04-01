<?php

/**
 * Description of Generator
 *
 * @author actcontpr
 */
class Generator {
	private $db;
	public function __construct(&$pdoConnection)
	{
		$this->db = $pdoConnection;
	}
	public function genClassesFromDb()
	{
		if ('cli' === PHP_SAPI) 
		{ 
			$lineBreak = "\n";
		} else {
			$lineBreak = "<br/>\n";
		}
		$el = $this->getEntityList();
		foreach ($el as $entityName)
		{
			$className = ucfirst($entityName);
			$attribs = $this->getEntityAttributeList($entityName);
			echo "writing classes/$className.php$lineBreak";
			file_put_contents(
						"classes/$className.php",
						$this->makeTypeClass($className, $attribs, $entityName)
				);
		}
		echo "finished writing classes$lineBreak";
	}
	public function getEntityClassNamesFromDb()
	{
		$el = $this->getEntityList();
		foreach ($el as &$entityName)
		{
			$entityName = ucfirst($entityName);
		}
		return $el;
	}
	public function clearAndInitDbForTestData()
	{
		$el = $this->getEntityList();
		foreach($el as $eName)
		{
			$this->db->query("TRUNCATE TABLE $eName");
			$this->db->query("ALTER TABLE $eName AUTO_INCREMENT = 1");
		}
	}
	private function mayOverride($className)
	{
		$rM = false;
		//check if classfile exists
		//instance class
		//
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
			$rM[$attrib['Field']] = $attrib['Type'];
		}
		return $rM;
	}
	public function makeTypeClass($className, $attribs, $entityName)
	{
		$classDef = "";
		$varDef = $this->genFieldsVarDefCode($attribs);
		$fieldsArray = $this->genFieldsArrayCode($attribs);
		$insertCode = $this->genEntityToValueInsertArrayCode($attribs);
		$saveCode = $this->genUpdateSetFieldsCode($attribs);
		$addCode = $this->genInsertSqlCode($attribs);
		$labelsArray = $this->genLabelArrayCode($attribs);
		$updateCode = $this->getUpdateCode();
		
		$classDef .= "<?php
		//2ndLayer autogeneration of classes with DbConnector
	class $className
	{	
		//change this to false if you've modified the class
		//and don't want the Generator to override this class:
		public static \$mayOverride = true;
		
		const ENTITYNAME=\"$entityName\";
		private \$db;
$varDef
		public static \$myLabels = $labelsArray

		public function __construct(&\$db = false)
		{
			if(\$db)
			{
				\$this->setDb(\$db);
			}
		}

		public static function getMyEntityName(){return self::ENTITYNAME;}
		public function getId(){return \$this->id;}
		public function setDb(&\$db){\$this->db=\$db;}

		public function save()
		{
			\$sql = 'UPDATE '.self::ENTITYNAME.' SET ';
			\$sql .= \"
			$saveCode\";
				
			\$sql .=' WHERE id='.\$this->id;
			
			\$update = \$this->db->prepare(\$sql);
			\$entityToValueInsertArray = $insertCode;
			\$update->execute(\$entityToValueInsertArray);
		}

		public function add()
		{
			$addCode
			\$insert = \$this->db->prepare(\$sql);
			\$entityToValueInsertArray = $insertCode;
			\$insert->execute(\$entityToValueInsertArray);
			\$this->id = \$this->db->lastInsertId();
		}
		$updateCode
		public static function getMyFields()
		{
			return $fieldsArray
		}
	}";
		return $classDef;
	}
	private function genFieldsArrayCode($attribs)
	{
		$fc = "array(\n";
		foreach(array_keys($attribs) as $attrib)
		{
			if ("id" != $attrib)
			{
				$fc .= '				"'.$attrib.'",'."\n";
			}
		}
		$rM = substr($fc,0,-2);
		$rM .= "\n			);";
		return $rM;
	}
	private function genLabelArrayCode($attribs)
	{
		$fc = "array(\n";
		foreach(array_keys($attribs) as $attrib)
		{
			if (
				("id" != $attrib)
				&&
				("isTest" != $attrib)
			)
			{
				$fc .= '			"'.$attrib.'" => "'.  ucfirst($attrib).'",'."\n";
			}
		}
		$rM = substr($fc,0,-2);
		$rM .= "\n		);";
		return $rM;
	}
	private function genFieldsVarDefCode($attribs)
	{
		$varDef = "";
		foreach(array_keys($attribs) as $attrib)
		{
			if ("id" == $attrib)
			{
				$access = "private";
			} else {
				$access = "public";
			}
			$varDef .= "		$access \$".$attrib.";\n";
		}
		return $varDef;
	}
	private function genEntityToValueInsertArrayCode($attribs)
	{
		$rM = "array(\n";
		foreach($attribs as $key => $val)
		{
			if("id" != $key)
			{
				$rM .= "				\"$key\" => \$this->".$key.", \n";
			}
		}
		$rM = substr($rM,0,-3);
		$rM .= "\n			)";
		return $rM;
	}
	private function genUpdateSetFieldsCode($attribs)
	{
		$rM = "";
		foreach(array_keys($attribs) as $attrib)
		{
			if ("id" != $attrib)
			{
				$rM .= "				$attrib=:$attrib, \n";
			}
		}
		$rM = substr($rM,0,-3);
		$rM = substr($rM,3);
		return $rM;
	}
	private function genInsertSqlCode($attribs)
	{
		$rM = '$sql = "INSERT INTO ".self::ENTITYNAME." ("'.";\n";
		$thisSql = "";
		foreach(array_keys($attribs) as $attrib)
		{
			if ("id" != $attrib)
			{
				$thisSql .= "			$attrib,\n";
			}
		}
		$thisSql = substr($thisSql,0,-2);
		$thisSql = substr($thisSql,3);
		$thatSql = "";
		foreach(array_keys($attribs) as $attrib)
		{
			if ("id" != $attrib)
			{
				$thatSql .= "			:$attrib,\n";
			}
		}
		$thatSql = substr($thatSql,0,-2);
		$thatSql = substr($thatSql,3);
		$thisSql .= "\n\n			) VALUES (\n\n			$thatSql\n			)";
		$rM .= "		\$sql .= \"$thisSql\";\n";
		return $rM;
	}
	private function getUpdateCode()
	{
		return "
	public function refresh()
	{
		\$this->load(\$this->id);
	}
	public function load(\$id)
	{
		\$sql = \"SELECT * FROM \".self::ENTITYNAME.\" WHERE id=\$id\";
		\$single = \$this->db->prepare(\$sql);
		\$single->execute();
		\$fieldVals = \$single->fetch(PDO::FETCH_ASSOC);
		foreach(\$fieldVals as \$field => \$val)
		{
			\$this->{\$field} = \$val;
		}
	}
	public function isNew()
	{
		#write your own code for preventing dupe entries here:
		return true;
	}";
	}
}
