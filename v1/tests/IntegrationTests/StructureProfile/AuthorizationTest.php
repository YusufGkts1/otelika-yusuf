<?php

use model\StructureProfile\application\CustomFeatureManagementService;

use model\IdentityAndAccess\application\IdentityService;

/**
 * ?*TEST
 * 		Sistem tarafindan eklenen Comment, Attachment, 
 * 		Tag, CustomFeature ve CustomFeatureAttachment
 * 		departmana kayitli kisiler tarafindan erisilebilir
 * 		olmali, problem cikarmamali.
 */
class AuthorizationTest extends TestCase {

	private DB $db_gis;
	private DB $db_iaa;

	function __construct() {
		global $framework;

		parent::__construct($framework);
	}

	protected function setUpOnce() {
		$this->db_gis = new DB(
			$this->config->get('db_gis_type'),
			$this->config->get('db_gis_hostname'),
			$this->config->get('db_gis_username'),
			$this->config->get('db_gis_password'),
			$this->config->get('db_gis_database'),
			$this->config->get('db_gis_port')
		);

		$this->db_iaa = new DB(
			$this->config->get('db_iaa_type'),
			$this->config->get('db_iaa_hostname'),
			$this->config->get('db_iaa_username'),
			$this->config->get('db_iaa_password'),
			$this->config->get('db_iaa_database'),
			$this->config->get('db_iaa_port')
		);

		// setup db_gis
		$this->db_gis->command("DELETE FROM custom_feature_category");
		$this->db_gis->command("DELETE FROM custom_feature");
		$this->db_gis->command("DELETE FROM custom_feature_variation");

		// setup db_iaa
		$this->db_iaa->command("DELETE FROM department");
		$this->db_iaa->command("DELETE FROM personnel");

		$this->db_iaa->command("
			INSERT INTO department
				(id, name, parent_id, director, director_allowed_parent_depth, director_allowed_child_depth, member_allowed_parent_depth, member_allowed_child_depth)
			VALUES
				(1, 'baskan', NULL, NULL, 1, 1, 1, 1),
				(2, 'baskan_yardimcisi_tahir', 1, NULL, 1, 1, 1, 1),
				(3, 'baskan_yardimcisi_riza, 1, NULL, 1, 1, 1, 1),
				(4, 'baskan_yardimcisi_ilgisiz', 1, NULL, 1, 1, 1, 1),
				(5, 'mudurluk_kultur', 2, NULL, 1, 1, 1, 1),
				(6, 'mudurluk_imar', 3, NULL, 1, 1, 1, 1),
				(7, 'mudurluk_emlak', 3, NULL, 1, 1, 1, 1),
				(8, 'mudurluk_ilgisiz', 4, NULL, 1, 1, 1, 1),
				(9, 'seflik_kultur', 5, NULL, 1, 1, 1, 1),
				(10, 'seflik_imar', 6, NULL, 1, 1, 1, 1),
				(11, 'seflik_emlak', 7, NULL, 1, 1, 1, 1),
				(12, 'seflik_ilgisiz', 8, NULL, 1, 1, 1, 1)
		");

		// create users
		$this->load->module('IdentityAndAccess');
		
		/** @var IdentityService $identity_service */
		$identity_service = $this->module_identity_and_access->service('Identity');

		$identity_service->registerPersonnel(null, null, 1, 'baskan', 'baskan', '19928392819', 'male', '28829183928', 'baskan@baskan.com', true);
		$identity_service->registerPersonnel(null, null, 2, 'yardimci', 'tahir', '11928392819', 'male', '28829183922', 'tahir@yardimci.com', true);
		$identity_service->registerPersonnel(null, null, 3, 'yardimci', 'riza', '19928392829', 'male', '28829183938', 'riza@yardimci.com', true);
		$identity_service->registerPersonnel(null, null, 4, 'yardimci', 'ilgisiz', '19928392329', 'male', '28829183931', 'ilgisiz@yardimci.com', true);
		$identity_service->registerPersonnel(null, null, 5, 'mudur', 'kultur', '19928322829', 'male', '23829183938', 'kultur@mudur.com', true);
		$identity_service->registerPersonnel(null, null, 6, 'mudur', 'imar', '19928312829', 'male', '23829123938', 'imar@mudur.com', true);
		$identity_service->registerPersonnel(null, null, 7, 'mudur', 'emlak', '19921322829', 'male', '22229183938', 'emlak@mudur.com', true);
		$identity_service->registerPersonnel(null, null, 8, 'mudur', 'ilgisiz', '19228322829', 'male', '23829113938', 'ilgisiz@mudur.com', true);
		$identity_service->registerPersonnel(null, null, 9, 'seflik', 'kultur', '11128322829', 'male', '233229183938', 'kultur@seflik.com', true);
		$identity_service->registerPersonnel(null, null, 10, 'seflik', 'imar', '11145322829', 'male', '2332291123128', 'imar@seflik.com', true);
		$identity_service->registerPersonnel(null, null, 11, 'seflik', 'emlak', '11121122829', 'male', '223221183938', 'emlak@seflik.com', true);
		$identity_service->registerPersonnel(null, null, 12, 'seflik', 'ilgisiz', '11121111829', 'male', '232229435938', 'ilgisiz@seflik.com', true);

		$salt = 'lAW1o59lg';
		$password = '6500523e66577e8ec2d85ae8298eecde0f5250ae';  // root

		$this->db_iaa->command("UPDATE `login` SET salt = :salt, password = :password", array(
			':salt' => $salt,
			':password' => $password
		));
	}

	public function testUploaderIdSavesCorrectly() {
		$this->load->module('StructureProfile');

		$this->setOperator(5, 2);

		/** @var CustomFeatureManagementService $cfm_service */
		$cfm_service = $this->module_structure_profile->service('CustomFeatureManagementService');

		$cfm_service->createCustomFeature('test_category', 'test_title', '{"type":"Point","coordinates":[28.868130015,41.023224594]}', 'test_description', null, null);

		// echo PHP_EOL . 'loaded successfully' . PHP_EOL;

		$this->assertTrue(true);
	}

	public function testPersonnelWillOnlyReceiveAuthorizedCustomFeatureCategories() {
		$this->assertTrue(true);
	}
}

?>