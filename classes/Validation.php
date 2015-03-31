<?php
	//2ndLayer Validation Class - This class holds a single Validation process for storage in the validation chain (data only)
	class Validation {
		public $fieldName = "";
		public $value = "";
		public $assertion = "";
		public $bounceMessage = "";
		public $bounceCssClass = "";

		public function __construct(
			$fieldName,
			$value = null,
			$assertion,
			$bounceMessage, 
			$bounceCssClass
		){
			$this->fieldName = $fieldName;
			$this->value = $value;
			$this->assertion = $assertion;
			$this->bounceMessage = $bounceMessage;
			$this->bounceCssClass = $bounceCssClass;
		}
	}