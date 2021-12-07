<?php

use model\Polling\application\DirectMessageProcessingService;
use model\Polling\application\IFlagManager;
use model\Polling\application\IIdentityProvider;
use model\Polling\application\MessagingService;
use model\Polling\application\QueueProcessingService;
use model\Polling\application\SurveyManagementService;
use model\Polling\application\TagRuleManagementService;
use model\Polling\domain\model\filter\IFilterTemplateRepository;
use model\Polling\domain\model\sms\CarrierResult;
use model\Polling\domain\model\sms\ICarrier;
use model\Polling\domain\model\sms\IDirectMessageRepository;
use model\Polling\domain\model\sms\IMessageTemplateRepository;
use model\Polling\domain\model\sms\Message;
use model\Polling\domain\model\survey\ISurveyLinkProvider;
use model\Polling\domain\model\survey\ISurveyRepository;
use model\Polling\domain\model\survey\ISurveySingleUseTokenGenerator;
use model\Polling\infrastructure\CitizenRepository;
use model\Polling\infrastructure\DirectMessageRepository;
use model\Polling\infrastructure\FilterStatementBuilder;
use model\Polling\infrastructure\QueueRepository;
use model\Polling\infrastructure\SurveyRepository;
use model\Polling\infrastructure\SurveySingleUseTokenGenerator;
use model\Polling\infrastructure\SurveyValidator;

class TagRuleTest extends TestCase {

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

		$this->db->command("DELETE FROM citizen");
		$this->db->command("DELETE FROM citizen_survey");
		$this->db->command("DELETE FROM `submission`");
		$this->db->command("DELETE FROM submission_response");
		$this->db->command("DELETE FROM survey");
		$this->db->command("DELETE FROM `tag`");
		$this->db->command("DELETE FROM `tag_bin`");
		$this->db->command("DELETE FROM tag_citizen");
		$this->db->command("DELETE FROM tag_citizen_bin");
		$this->db->command("DELETE FROM tag_rule");
		$this->db->command("DELETE FROM tag_rule_bin");
		$this->db->command("DELETE FROM tag_rule_survey");
		$this->db->command("DELETE FROM tag_rule_survey_bin");
		$this->db->command("DELETE FROM tag_rule_tag");
		$this->db->command("DELETE FROM tag_rule_tag_bin");
		$this->db->command("DELETE FROM `token`");

		foreach($this->sampleCitizenData() as $c)
			$this->db->insert('citizen', [
				'id' => $c['id'],
				'kimlik_no' => $c['kimlik_no'],
				'ad' => $c['ad'],
				'soyad' => $c['soyad'],
				'cinsiyet' => $c['cinsiyet'],
				'telefon' => $c['telefon'],
				'dogum_tarih' => $c['dogum_tarih']
			]);
	}

	private function sampleCitizenData() {
		return [[
			'id' => '11111111111',
			'kimlik_no' => '11111111111',
			'ad' => 'firstname 1',
			'soyad' => 'lastname 1',
			'cinsiyet' => '1',
			'telefon' => '11111',
			'dogum_tarih' => '2000-01-01'
		], [
			'id' => '22222222222',
			'kimlik_no' => '22222222222',
			'ad' => 'firstname 2',
			'soyad' => 'lastname 2',
			'cinsiyet' => '2',
			'telefon' => '22222',
			'dogum_tarih' => '2000-01-01'
		], [
			'id' => '33333333333',
			'kimlik_no' => '33333333333',
			'ad' => 'firstname 3',
			'soyad' => 'lastname 3',
			'cinsiyet' => '1',
			'telefon' => '33333',
			'dogum_tarih' => '2000-01-01'
		], [
			'id' => '44444444444',
			'kimlik_no' => '44444444444',
			'ad' => 'firstname 4',
			'soyad' => 'lastname 4',
			'cinsiyet' => '2',
			'telefon' => '44444',
			'dogum_tarih' => '2000-01-01'
		], [
			'id' => '55555555555',
			'kimlik_no' => '55555555555',
			'ad' => 'firstname 5',
			'soyad' => 'lastname 5',
			'cinsiyet' => '1',
			'telefon' => '55555',
			'dogum_tarih' => '2000-01-01'
		]];
	}

	private function surveyManagementService() : SurveyManagementService {
		$this->load->module('Polling');

		return $this->module_polling->service('SurveyManagementService');
	}

	// private function messagingService() : MessagingService {
	// 	$this->load->module('Polling');

	// 	return $this->module_polling->service('MessagingService');
	// }

	private function queueRepository() {
		return new QueueRepository(
			$this->db,
			$this->citizenRepository()
		);
	}

	private function directMessageRepository() {
		return new DirectMessageRepository(
			$this->db,
			$this->db,
			$this->citizenRepository()
		);
	}

	private function messageTemplateRepository() {
		return $this->createMock(IMessageTemplateRepository::class);
	}

	private function filterTemplateRepository() {
		return $this->createMock(IFilterTemplateRepository::class);
	}

	private function citizenRepository() {
		return new CitizenRepository(
			$this->db,
			new FilterStatementBuilder(
				$this->db,
				$this->surveyValidator()
			)
		);
	}

	private function surveyRepository() {
		return new SurveyRepository(
			$this->db
		);
	}

	private function tokenGenerator() {
		return new SurveySingleUseTokenGenerator();
	}

	private function surveyLinkProvider() {
		return $this->createMock(ISurveyLinkProvider::class);
	}

	private function identityProvider() {
		return $this->createMock(IIdentityProvider::class);
	}

	private function surveyValidator() : SurveyValidator {
		return $this->createMock(SurveyValidator::class);
	}

	private function carrier() {
		return $this->createMock(ICarrier::class);
	}

	private function flagManager() {
		$flag_manager = $this->createMock(IFlagManager::class);

		$flag_manager->method('isSet')->willReturn(false);

		return $flag_manager;
	}

	private function messagingService(?ICarrier $carrier = null) {
		return new MessagingService(
			$this->queueRepository(),
			$this->directMessageRepository(),
			$this->messageTemplateRepository(),
			$this->FilterTemplateRepository(),
			$this->citizenRepository(),
			$this->surveyRepository(),
			$carrier ?? $this->carrier(),
			$this->tokenGenerator(),
			$this->surveyLinkProvider(),
			$this->identityProvider()
		);
	}

	private function directMessageProcessingService(?ICarrier $carrier) {
		return new DirectMessageProcessingService(
			$this->directMessageRepository(),
			$carrier ?? $this->carrier(),
			$this->flagManager()
		);
	}

	// private function tagRuleManagementService() {
	// 	return new TagRuleManagementService(
	// 		$this->tagRuleRepository(),
	// 		$this->tagRepository()
	// 	)
	// }

	// private function queueProcessingService(?ICarrier $carrier = null) {
	// 	return new QueueProcessingService(
	// 		$carrier ?? $this->carrier(),
	// 		$this->queueRepository(),
	// 		$this->tokenGenerator(),
	// 		$this->surveyLinkProvider(),
	// 		$this->flagManager(),
	// 		null
	// 	);
	// }

	private function sampleFormData() {
		return json_decode('
			{
				"attributes": [
					{
						"name": "field1",
						"type": "number",
						"required": true
					},
					{
						"name": "field2",
						"type": "radio",
						"required": true,
						"values": [
					{
								"label": "Seçim 1",
								"value": "1"
							},
							{
								"label": "Seçim 2",
								"value": "2"
							},
							{
								"label": "Seçim 3",
								"value": "3"
							}		
						],
						"value": 2
					}
				]
			}
		', true);
	}

	public function test_survey_is_created_successfully() {
		$sample_form_data = $this->sampleFormData(); 

		$id = $this->surveyManagementService()->create(
			'test_survey',
			$sample_form_data
		);

		$this->assertSurveyExists($id, 'test_survey', $sample_form_data);

		return $id;
	}

	/**
     * @depends test_survey_is_created_successfully
     */
	public function test_survey_is_sent_to_citizens($id) {
		$token = '';

		$carrier = $this->createMock(ICarrier::class);
		$carrier->expects($this->exactly(1))
				->method('send')
				->with($this->callback(function(Message $message) use (&$token) {
					$this->assertTrue($message->surveyTokenAttached());

					$token = $message->token();

					return true;
				}))
				->willReturn(new CarrierResult(true, 'success'));

		$this->messagingService()->sendCustomMessageToSingleCitizen(
			'11111111111',
			'anket test.',
			$id
		);

		$this->directMessageProcessingService($carrier)->processDirectMessages(10);

		return $token;
	}

	// /**
    //  * @depends test_survey_get_created_successfully
    //  */
	// public function test_citizens_get_tagged_when_a_rule_is_created() {

	// }

	private function assertSurveyExists($id, $name, $form) {
		$s = $this->db->query("SELECT * FROM survey WHERE id = :id", [
			':id' => $id
		])->row;

		$this->assertEquals($name, $s['name']);
		$this->assertEqualsCanonicalizing($form, json_decode($s['form'], true));
	}
}

?>