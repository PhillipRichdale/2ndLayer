<?php
	//2ndLayer - This class handles all aspect of server-side validation
	require_once 'Validation.php';
	class Validator {
		private $validationChain;
		public $bounces;
		public $pass = true;

		public function __construct()
		{
			$this->initValidation();
		}
		public function initValidation()
		{
			$this->validationChain = array();
			$this->bounces = array();
		}
		public function addValidation($fieldName, $value, $assertion, $bounceMessage, $bounceCssClass = "bounce")
		{
			$validation = new Validation($fieldName, $value, $assertion, $bounceMessage, $bounceCssClass = "bounce");
			$this->validationChain[] = $validation;
		}
		public function validate()
		{
			foreach($this->validationChain as $validation)
			{
				if (!$this->{$validation->assertion}($validation->value))
				{
					$this->bounces[$validation->fieldName] = $validation;
					$this->pass = false;
				}
			}
			return $this->pass;
		}

		//##############################################################################

		//HTML Output Helpers:
		public function insertCss($fieldName, $insert="bounce")
		{
			if(array_key_exists($fieldName, $this->bounces))
			{
				$retVal = " $insert";
			} else {
				$retVal = "";
			}
			return $retVal;
		}
		public function appendCss($fieldName, $regularCssClass="form-entry")
		{
			if(array_key_exists($fieldName, $this->bounces))
			{
				$retVal = $regularCssClass." ".$this->bounces[$fieldName]->bounceCssClass;
			} else {
				$retVal = $regularCssClass;
			}
			return $retVal;
		}
		public function toggleCss($fieldName, $regularCssClass="form-entry")
		{
			if(array_key_exists($fieldName, $this->bounces))
			{
				$retVal = $this->bounces[$fieldName]->bounceCssClass;
			} else {
				$retVal = $regularCssClass;
			}
			return $retVal;
		}
		public function getBounceMessages()
		{
			$lines = "";
			foreach($this->bounces as $bounce)
			{
				$lines .= '<li class="alert">'.$bounce->bounceMessage."</li>\n";
			}
			if (strlen($lines)>0)
			{
				return "<ul>\n".$lines."</ul>";
			} else {
				return $lines;
			}
		}

		//##############################################################################

		//assertions (positive is allways a validation pass!):
		public function assertIsEmail($email)
		{
			/**
			Code provided By Douglas Lovell (Linux Journal) - Jun 01, 2007
			Validate an email address.
			Provide email address (raw input)
			Returns true if the email address has the email 
			address format and the domain exists.
			**/
			$isValid = true;
			$atIndex = strrpos($email, "@");
			if (is_bool($atIndex) && !$atIndex)
			{
				$isValid = false;
			}
			else
			{
				$domain = substr($email, $atIndex+1);
				$local = substr($email, 0, $atIndex);
				$localLen = strlen($local);
				$domainLen = strlen($domain);
				if ($localLen < 1 || $localLen > 64)
				{
					// local part length exceeded
					$isValid = false;
				}
				else if ($domainLen < 1 || $domainLen > 255)
				{
					// domain part length exceeded
					$isValid = false;
				}
				else if ($local[0] == '.' || $local[$localLen-1] == '.')
				{
					// local part starts or ends with '.'
					$isValid = false;
				}
				else if (preg_match('/\\.\\./', $local))
				{
					// local part has two consecutive dots
					$isValid = false;
				}
				else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
				{
					// character not valid in domain part
					$isValid = false;
				}
				else if (preg_match('/\\.\\./', $domain))
				{
					// domain part has two consecutive dots
					$isValid = false;
				}
				else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local)))
				{
					// character not valid in local part unless 
					// local part is quoted
					if (!preg_match('/^"(\\\\"|[^"])+"$/',
						 str_replace("\\\\","",$local)))
					{
						$isValid = false;
					}
				}
				if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
				{
					// domain not found in DNS
					$isValid = false;
				}
			}
			return $isValid;
		}
		public function assertIsNotEmpty($str)
		{
			return (strlen($str) > 0);
		}
		public function assertIsSet($str)
		{
			return (strlen($str) > 0);
		}
		public function assertIsValidPhone($str)
		{
			$retVal = false;
			if($this->assertIsNotEmpty($str))
			{
				$str = str_replace("	", "", $str);
				$str = str_replace(" ", "", $str);
				$str = str_replace("-", "", $str);
				$str = str_replace("(", "", $str);
				$str = str_replace(")", "", $str);
				$str = preg_replace("/\d/", "", $str);
				$retVal = (0 == strlen($str));
			} else {
				$retVal = false;
			}
			return $retVal;
		}
		public function assertIsPlz($str)
		{
			$retVal = false;
			if($this->assertIsNotEmpty($str))
			{
				$str = str_replace(" ", "", $str);
				$str = str_replace("	", "", $str);
				$str = str_replace("-", "", $str);
				$str = str_replace("D", "", $str);
				$str = preg_replace("/\d\d\d\d\d/", "", $str);
				$retVal = (0 == strlen($str));
			} else {
				$retVal = false;
			}
			return $retVal;
		}
	}
