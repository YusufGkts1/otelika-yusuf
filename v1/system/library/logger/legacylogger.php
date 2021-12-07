<?php 

namespace Logger;

use Log;
use Logger;

class LegacyLogger implements Logger {

	private Log $sentry;

	function __construct(Log $log) {
		$this->log = $log;
	}

	public function log(string $message) {
		$this->log->write($message);
	}
}

?>