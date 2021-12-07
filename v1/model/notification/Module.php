<?php 

use \model\notification\Mail;
use \model\notification\Sms;

class ModuleNotification extends Module {

	private $map;

	protected function initialize() : void { 

		$this->map = [
			'Mail' => function() {
				return new Mail();
			},
			'Sms' => function(){
				return new Sms();
			}
		];
	}

	protected function serviceProvider(string $identifier) : object {
		return $this->map[$identifier]();
	}
}

?>