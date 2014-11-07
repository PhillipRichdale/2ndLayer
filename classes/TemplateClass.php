<?php
	//2ndLayer TemplateClass for autogeneration:
	class TemplateClass {
		const ENTITYNAME="templateentity";
		
		private $db;
		private $id;

		public $attrib1;
		public $attrib2;
		public $attrib3;
		
		public $_role1;
		public $_role2;
		
		public static $myLabels = array(
			"attrib1" => "Attrib1",
			"attrib2" => "Attrib2",
			"attrib3" => "Attrib3",
			"_role1" => "Role1",
			"_role2" => "Role2"
		);
		
		public function __construct(&$db = false)
		{
			if($db)
			{
				$this->setDb($db);
			}
			
			//0-1, 0-*, 1, 1-*,
			$this->_role1 = function()
			{
				$sql = "SELECT * FROM farEntity WHERE templateentity_id=$id";
				$dataFetch = $this->db->prepare($sql);
				$dataFetch->execute();
				return $dataFetch->fetchAll(PDO::FETCH_ASSOC);
			};
			
			//role with n:m-rel
			$this->_role2 = function()
			{
				$sql = "SELECT [FarId] FROM thisEntity_role2 WHERE templateentity_id=$id";
			};
		}
		
		public static function getMyEntityName(){return self::ENTITYNAME;}
		public function getId(){return $this->id;}
		public function setDb(&$db){$this->db=$db;}
		public function refresh(){$this->load($this->id);}
		
		public function load($id)
		{
			$sql = "SELECT * FROM ".self::ENTITYNAME." WHERE id=$id";
			$single = $this->db->prepare($sql);
			$single->execute();
			$fieldVals = $single->fetch(PDO::FETCH_ASSOC);
			foreach($fieldVals as $field => $val)
			{
				$this->{$field} = $val;
			}
		}
		public function isNew()
		{
			#write your own code for preventing dupe entries here:
			return true;
		}
		public static function getMyFields()
		{
			return array(
				"field1",
				"field2",
				"field3"
			);
		}
		
		public function save()
		{
			$sql = 'UPDATE '.self::ENTITYNAME.' SET ';
			$sql .= "
				field1=:attrib1,
				field2=:attrib2,
				field3=:attrib3";
			$sql .=' WHERE id='.$this->id;
			$update = $this->db->prepare($sql);
			$entityToValueInsertArray = array(
				"field1" => $this->attrib1,
				"field2" => $this->attrib2,
				"field3" => $this->attrib3
			);
			$update->execute($entityToValueInsertArray);
		}

		public function add()
		{
			$sql = "INSERT INTO ".self::ENTITYNAME;
			$sql .= "(
					field1,
					field2,
					field3
				) VALUES (
					:attrib1,
					:attrib2,
					:attrib3
				)";

			$insert = $this->db->prepare($sql);
			$entityToValueInsertArray = array(
				"attrib1" => $this->attrib1, 
				"attrib2" => $this->attrib2, 
				"attrib3" => $this->attrib3
			);
			$insert->execute($entityToValueInsertArray);
			$this->id = $this->db->lastInsertId();
		}
		
		public function _role1()
		{
			
		}
	}