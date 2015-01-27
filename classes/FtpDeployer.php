<?php
	//2ndLayer FtpDeployer is a class that uses Git CLI to determine a diff between
	//two versions, each stated by commitcode or tag, and put that set of differences
	//on a target FTP location. The first version of this is the base functionality for
	//perhaps a larger deployment toolkit in future versions in 2ndLayer.

	class FtpDeployer
	{
		private $pathToRepoDir;
		private $fromCommit;
		private $toCommit;
		
		private $ftpHost;
		private $ftpUser;
		private $ftpPw;
		private $remoteFtpRootPathToBaseDir;
		
		private $ftpStream;
		
		//git --git-dir=[PathToRepo]/.git diff-tree -r --no-commit-id --name-only --diff-filter=ACMRT [FromCommit] [ToCommit]
		public function __construct($pathToRepoDir, $fromCommit, $toCommit)
		{
			$this->pathToRepoDir = $pathToRepoDir;
			$this->fromCommit = $fromCommit;
			$this->toCommit = $toCommit;
		}
		public function setFtp($ftpHost, $ftpUser, $ftpPw, $remoteFtpRootPathToBaseDir)
		{
			$this->ftpHost = $ftpHost;
			$this->ftpUser = $ftpUser;
			$this->ftpPw = $ftpPw;
			$this->remoteFtpRootPathToBaseDir = $remoteFtpRootPathToBaseDir;
		}
		public function deploy()
		{
			try {
				$this->ftpStream = ftp_connect($this->ftpHost);
			} catch (Exception $e) {
				echo "FTP stream couldn't be opened. -> ".$e->getMessage();
			}
			
			try {
				
			} catch (Exception $e) {
				
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
		private function makeDiffList()
		{
			return exec (
					"git --git-dir=".$this->pathToRepoDir.
					"/.git diff-tree -r --no-commit-id --name-only --diff-filter=ACMRT ".
					$this->fromCommit." ".$this->fromCommit
			);
		}
		//
	}