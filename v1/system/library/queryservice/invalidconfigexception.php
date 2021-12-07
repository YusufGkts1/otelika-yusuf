<?php 

namespace queryservice;

class InvalidConfigException extends \Exception {
	
	function __construct($message) {
		$this->message = $message;
	}
}

?>