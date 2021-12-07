<?php 

namespace Logger;

use Logger;
use Sentry;

class SentryLogger implements Logger {

	private Sentry $sentry;

	function __construct(Sentry $sentry) {
		$this->sentry = $sentry;
	}

	public function log(string $message) {
		$this->sentry->captureException(new \Exception($message));
	}
}

?>