<?php 

class InternalError extends Exception {
	function __construct($message = "Internal error") {
		parent::__construct($message);
	}
}

?>