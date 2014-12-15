<?php
	//2ndLayer SimpleCache class for caching PHP Objects:
	class SimpleCache
	{
		public $cacheExpire = 1800; //30 minutes
		private $db;
		public function __construct(&$db)
		{
			$this->db = $db;
			$this->createCacheTable();
		}
		public function cache($data, $name = false)
		{
			$dataClean = serialize($data);
			if(!$name)
			{
				throw new Exception('No name for data passed to 2ndLayer SimpleCache::cache($data, $name = false)');
			} else {
				// MyGuests  lastname='Doe' WHERE id=2
				$sql = "UPDATE SIMPLECACHE SET data='$dataClean', timestamp=CURRENT_TIMESTAMP WHERE name='$name'";
				$result = $this->db->query($sql);
			}
		}
		public function get()
		{
			$rm = false;
			$sql = "SELECT * FROM SIMPLECACHE WHERE name='$name'";
			$result = $this->db->query($sql);
			if ($result->rowCount() > 0)
			{
				$assoc = $result->fetch(PDO::FETCH_ASSOC);
				$rm = unserialize($assoc['data']);
			}
			return $rm;
		}
		private function cacheHasExpired($name)
		{
			$rm = false;
			$sql = "select timestamp from SIMPLECACHE where name='$name'";
			$result = $this->db->query($sql);
			if ($result->rowCount > 0)
			{
				$assoc = $result->fetch(PDO::FETCH_ASSOC);
				$rm = $assoc['timestamp'] < time() - $this->cacheExpire;
			}
			return $rm;
		}
		private function createCacheTable()
		{
			if(!$this->cacheTableExists())
			{
				//data attribute has 16MB storage:
				$sql = "CREATE TABLE `SIMPLECACHE` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`name` varchar(1000) COLLATE utf8_unicode_ci NOT NULL,
					`data` mediumtext COLLATE utf8_unicode_ci NOT NULL,
					`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
					) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

					ALTER TABLE `SIMPLECACHE`
					ADD PRIMARY KEY (`id`), ADD FULLTEXT KEY `NAME_INDEX` (`name`)";
				$dataFetch = $this->db->query($sql);
				$dataFetch->execute();
			}
		}
		private function cacheTableExists()
		{
			return $this->db->query("SHOW TABLES LIKE 'SIMPLECACHE'")->rowCount() > 0;
		}
		private function nameExists($name)
		{
			return $this->getRowCount($name) > 0;
		}
		private function getRowCount($name)
		{
			return $this->db->query("SELECT * FROM SIMPLECACHE WHERE name='$name'")->rowCount();
		}
		private function removeExcessRows($name)
		{
			if($this->getRowCount($name) > 1)
			{
				$assocList = $this->db->query("select * from SIMPLECAHCE where name='name'")->fetchAll(PDO::FETCH_ASSOC);
				
				function thisCompare($a, $b) {
					if ($a['timestamp'] == $b['timestamp'])
					{
						return 0;
					}
					//return $a['timestamp'] < $b['timestamp'] ? -1 : 1;
					
					//producing a reverse order array:
					return $a['timestamp'] > $b['timestamp'] ? -1 : 1;
				}
				usort($assocList, "thisCompare");
				for($i = 1; $i < count($assocList); $i++)
				{
					$this->db->query("DELETE FROM SIMPLECACHE WHERE id=".$assocList[$i]['id']);
				}
			}
		}
	}