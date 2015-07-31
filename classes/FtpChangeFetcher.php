<?php
	//2ndLayer FtpChangeFetcher is a class that uses a set of yet to be determined CLI tools
	//to compare the contents of a remote FTP-mounted directory with the contents of a local 
	//directory and download those contents that are newer/have been changed/touched compared
	//with the local files and directories.
	//It dumpes a list of the files updated from the remote location either to standard out or
	//a textfile, for further processing by versioning and/or distribution/deployment systems.

	class FtpChangeFetcher
	{	
		private $ftpHost;
		private $ftpUser;
		private $ftpPw;
		private $remoteFtpRootPathToBaseDir;
		private $remoteRelativeFtpPath;
		
		private $ftpStream;
		
		public function __construct()
		{
		}
		public function setFtp($ftpHost, $ftpUser, $ftpPw, $remoteFtpRootPathToBaseDir)
		{
			$this->ftpHost = $ftpHost;
			$this->ftpUser = $ftpUser;
			$this->ftpPw = $ftpPw;
			$this->remoteFtpRootPathToBaseDir = $remoteFtpRootPathToBaseDir;
		}
		public function syncRemoteToLocal()
		{
			try {
				$this->ftpStream = ftp_connect($this->ftpHost);
			} catch (Exception $e) {
				echo "FTP stream couldn't be opened. -> ".$e->getMessage();
			}
			
			if (@ftp_login($this->ftpStream, $this->ftpUser, $this->ftpPw))
			{} else {
				echo "Couldn't log in on this FTP connection. User and/or PW are wrong.";
				exit();
			}
			
			$fileList = $this->makeDiffList();
			foreach($fileList as $localFileWithPath)
			{
				
				//ftp_put ( resource $ftp_stream , string $remote_file , string $local_file , int $mode [, int $startpos = 0 ] )
				ftp_put(
					$this->ftpStream ,
					$this->remoteFtpRootPathToBaseDir."/".basename($localFileWithPath),
					$localFileWithPath,
					FTP_BINARY
				);
			}
			
			ftp_close($this->ftpStream);
		}
	}