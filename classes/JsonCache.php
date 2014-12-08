<?php
	//2ndLayer JsonCache class for caching json output for ajax-driven UIs:
	class JsonCache
	{
		public $cacheExpire = 1800; //30 minutes
		private $db;
		public function __construct(&$db = false)
		{
			if($db)
			{
				$this->setDb($db);
				$this->createCacheTable();
			}
		}
		private function createCacheTable()
		{
			if(!$this->cacheTableExists())
			{
				//data attribute has 16MB storage:
				$sql = "CREATE TABLE `JSONCACHE` (
					`id` int(11) NOT NULL,
					`idString` varchar(1000) COLLATE utf8_unicode_ci NOT NULL,
					`data` mediumtext COLLATE utf8_unicode_ci NOT NULL,
					`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
				) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
				";
				$dataFetch = $this->db->query($sql);
				$dataFetch->execute();
			}
		}
		private function cacheTableExists()
		{
			$result = $this->db->query("SHOW TABLES LIKE 'JSONCACHE'");
			return $result->rowCount() > 0;
		}
	}