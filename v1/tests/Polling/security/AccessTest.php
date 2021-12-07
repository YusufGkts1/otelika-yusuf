<?php

use model\system\log\OperatorType;

class AccessTest extends TestCase {

	function __construct() {
		global $framework;

		parent::__construct($framework);
	}

	public function test_personnel_can_not_load_services_if_config_key_polling_allowed_personnels_is_missing() {
		$this->config = new Config();

		$this->load->module('Polling');

		$this->expectException(\AuthorizationException::class);

		$this->module_polling->service('MessagingService');
	}

	public function test_personnel_cannot_load_services_if_not_in_config_option_polling_allowed_personnels() {
		$this->load->module('Polling');

		$this->config->set('polling_allowed_personnels', [
			'1'
		]);

		$this->setOperator(2, 2);

		$this->expectException(\AuthorizationException::class);

		$this->module_polling->service('MessagingService');
	}

	public function test_personnel_listed_in_config_option_polling_allowed_personnels_can_load_services() {
		$this->load->module('Polling');

		$this->config->set('polling_allowed_personnels', [
			'1'
		]);

		$this->setOperator(1, 2);

		try {
			$this->module_polling->service('MessagingService');
		}
		catch(\AuthorizationException $e) {
			$this->fail('AuthorizationException occurred');
		}
		catch(\Throwable $t) {
			$this->assertTrue(true);
		}
	}

	public function test_operators_of_type_operation_can_load_services() {
		$this->load->module('Polling');

		$this->setOperator(0, OperatorType::Operation);

		try {
			$this->module_polling->service('QueueProcessingService');
		}
		catch(\AuthorizationException $e) {
			$this->fail('AuthorizationException occurred');
		}
		catch(\Throwable $t) {
			$this->assertTrue(true);
		}
	}
}

?>