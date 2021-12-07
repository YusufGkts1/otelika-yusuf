<?php 

/**
 * !!! Requires package sentry/sdk !!!
 */


class Sentry {
	function __construct($dsn) {
		\Sentry\init(array(
			'dsn' => $dsn
		));
	}

	public function captureException(Throwable $exception) {
		\Sentry\captureException($exception);
	}
}

?>