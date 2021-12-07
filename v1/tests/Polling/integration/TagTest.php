<?php

use model\Polling\application\TagManagementService;

class TagTest extends TestCase {

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

	private function tagManagementService() : TagManagementService {
		$this->load->module('Polling');

		return $this->module_polling->service('TagManagementService');
	}

	public function test_category_is_created_successfully() {
		$name = 'test_category';
		$color = '423423';
		$parent_id = null;

		$id = $this->tagManagementService()->createCategory(
			$name,
			$color,
			$parent_id
		);

		$this->assertCategoryEquals($id, $name, $color, $parent_id);
	}

	private function assertCategoryEquals($id, $name, $color, $parent_id) {
		$c = $this->db->query("SELECT * FROM category WHERE id = :id", [
			':id' => $id
		])->row;

		$this->assertEquals($name, $c['name']);
		$this->assertEquals($color, $c['color']);
		$this->assertEquals($parent_id, $c['parent_id']);
	}
}

?>