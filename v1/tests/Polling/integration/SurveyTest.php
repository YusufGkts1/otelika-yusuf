<?php

use model\Polling\application\SurveyManagementService;
use model\Polling\application\TagRuleManagementService;

class SurveyTest extends TestCase {

	private DB $db;

	function __construct() {
		global $framework;

		parent::__construct($framework);
	}

	protected function setUpOnce() {
		$this->db = new DB(
			$this->config->get('db_polling_type'),
			$this->config->get('db_polling_hostname'),
			$this->config->get('db_polling_username'),
			$this->config->get('db_polling_password'),
			$this->config->get('db_polling_database'),
			$this->config->get('db_polling_port')
		);

		$this->db->command("DELETE FROM survey");
	}

	private function surveyManagementService() : SurveyManagementService {
		$this->load->module('Polling');

		return $this->module_polling->service('SurveyManagementService');
	}

	public function test_survey_is_created_successfully() {
		$name = "testsurvey";
		$form = [
			'name' => 'testname',
			'description' => 'testcription',
			'attributes' => [
				[
					'type' => 'number',
					'required' => true,
					'name' => 'number-1'
				],
				[
					'type' => 'checkbox',
					'required' => true,
					'name' => 'checkbox-1',
					"values" => [
						[
							"label" => "opt 1",
							"value" => 1
						],
						[
							"label" => "opt 2",
							"value" => 2
						],
						[
							"label" => "opt 3",
							"value" => 3
						]
					]
				]
			]
		];
		
		$id = $this->surveyManagementService()->create(
			$name,
			$form
		);

		$this->assertSurveyEquals($id, $name, $form);
	}

	private function assertSurveyEquals($id, $name, $form) {
		$s = $this->db->query("SELECT * FROM survey WHERE id = :id", [
			':id' => $id
		])->row;

		// e20('--- passed ---');
		// e00('name: ' . $name);
		// e00('form: ' . json_encode($form));

		// e10('--- form ---');
		// e00('name: ' . $s['name']);
		// e02('form: ' . $s['form']);

		$this->assertEquals($name, $s['name']);
		$this->assertEqualsCanonicalizing($form, json_decode($s['form'], true));
	}
}

?>