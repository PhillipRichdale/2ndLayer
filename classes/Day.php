<?php
//2ndLayer - The Day class represents a day in the classic western calendar to be handled as one whishes (utilised in a calendar app, stored in the DB, etc.)
class Day {
	const ENTITYNAME="day";
	private $db;
	private $id;
	public $day;
	public $isTest;

	public $myLabels = array("day" => "Day");
	public $Months = array("-","January","February","March","April","May","June","July","August","September","October","November","December");
	public $Weekdays = array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
	
	//German labes. Uncomment if required.
	//public $myLabels = array("day" => "Tag");
	//public $Months = array("-","Januar","Februar","März","April","Mai","Juni","Juli","August","September","Oktober","November","Dezember");
	//public $Weekdays = array("Sonntag","Montag","Dienstag","Mittwoch","Donnerstag","Freitag","Samstag");
	
	public function __construct(&$db = false)
	{
		if($db)
		{
			$this->setDb($db);
		}
		//German shorthand:
		//$this->Weekdays = array("So.","Mo.","Di.","Mi.","Do.","Fr.","Sa.");
	}

	public static function getMyEntityName(){return self::ENTITYNAME;}
	public function getId(){return $this->id;}
	public function setDb(&$db){$this->db=$db;}

	public function getName($date = null)
	{
		if(is_null($date))
		{
			$date = new DateTime($this->day);
		}
		return $this->wochentag($date).", ".$date->format("j").". ".$this->monat($date)." ".$date->format("Y");
	}
	public function getArrayOfDays($startDay='2015-01-01', $endDay='2015-12-31')
	{
		$sql = "SELECT * FROM ".self::ENTITYNAME." WHERE DATE(day) >= DATE('$startDay') AND DATE(day) <= DATE('$endDay') ORDER BY DATE(day)";
		$pdoSet = $this->db->prepare($sql);
		$pdoSet->execute();
		$allArray = $pdoSet->fetchAll(PDO::FETCH_ASSOC);
		foreach($allArray as &$item)
		{
			$item['name'] = $this->getName(DateTime::createFromFormat('Y-m-d H:i:s', $item["day"]));
		}
		return $allArray;
	}
	public function getAvailableDaysDropDown($day_id)
	{
		require_once 'Event.php';
		$event = @new Event($this->db);
		$dayArray = $this->getArrayOfDays();
		$availDays = array();
		foreach($dayArray as $day)
		{
			if($event->getValidEventsLeft($day_id) > 0)
			{
				if($day["id"] == $day_id)
				{
					$day["htmlInsert"] = " selected";
				} else {
					$day["htmlInsert"] = "";
				}
				$availDays[] = $day;
			}
		}
		$dropDown = "<select class=\"form-select\" name=\"day_id\" id=\"day_id\" value=\"\">\n";
		foreach($availDays as $day)
		{
			$thisId = $day['id'];
			$thisInsert = $day['htmlInsert'];
			$thisName = $day['name'];
			$dropDown .= "	<option value=\"$thisId\"$thisInsert>$thisName</option>\n";
		}
		$dropDown .= "</select>\n";
		return $dropDown;
	}
	public function wochentag(DateTime $date)
	{
		#w	Numeric representation of the day of the week
		$i = $date->format("w");
		return $this->Weekdays[$i];
	}
	public function monat(DateTime $date)
	{
		#n	Numeric representation of a month, without leading zeros
		$i = $date->format("n");
		return $this->Monate[$i];
	}
	public function generateDays($start='2014-04-01', $end='2014-05-31')
	{
		$writeDays = true;
		$oneDay = new DateInterval("P1D");
		$thisDay = new DateTime($start);
		while($writeDays)
		{
			if(!$this->isWeekend($thisDay))
			{
				echo "writing day<br />";
				$this->day = $thisDay->format('Y-m-d H:i:s');
				$this->add();
			}
			$thisDay->add($oneDay);
			echo "start: ".$thisDay->getTimestamp()."end: ".strtotime($end)."<br />";
			if ($thisDay->getTimestamp() >= strtotime($end))
			{
				$writeDays = false;
			}
		}
	}
	public function isWeekend($date)
	{
		$w = $date->format("w");
		return (0 == $w) || (6 == $w);
	}
	public function save()
	{
		$sql = 'UPDATE '.self::ENTITYNAME.' SET ';
		$sql .= "day=:day, 
			isTest=:isTest";
		$sql .=' WHERE id='.$this->id;
		$update = $this->db->prepare($sql);
		$entityToValueInsertArray = array(
			"day" => $this->day, 
			"isTest" => $this->isTest
			);
		$update->execute($entityToValueInsertArray);
	}

	public function add()
	{
		$sql = "INSERT INTO ".self::ENTITYNAME." (";
		$sql .= "day,
			isTest

			) VALUES (

			:day,
			:isTest
			)";
		$insert = $this->db->prepare($sql);
		$entityToValueInsertArray = array(
			"day" => $this->day, 
			"isTest" => $this->isTest
			);
		$insert->execute($entityToValueInsertArray);
		$this->id = $this->db->lastInsertId();
	}

	public function refresh()
	{
		$this->load($this->id);
	}
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
	public static function getMyFields()
	{
		return array(
			"day",
			"isTest"
		);
	}
	public buildMyTable()
	{
		if ($this->db)
		{
			if (!$this->db->tableExists("day"))
			{
				$sql = "
					DROP TABLE IF EXISTS `day`;
					CREATE TABLE `day` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `day` datetime NOT NULL,
					  `isTest` tinyint(1) DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;";
				$pdoSet = $this->db->prepare($sql);
				return $pdoSet->execute();
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
}
