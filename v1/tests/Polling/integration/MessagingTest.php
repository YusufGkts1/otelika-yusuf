<?php

use tests\Polling\data\FilterProvider;
use tests\Polling\data\CitizenProvider;

use model\Polling\infrastructure\CitizenRepository;
use model\Polling\infrastructure\FilterStatementBuilder;
use model\Polling\infrastructure\QueueRepository;
use model\Polling\infrastructure\SurveyValidator;
use model\Polling\infrastructure\DirectMessageRepository;

use model\Polling\application\IFlagManager;
use model\Polling\application\IIdentityProvider;
use model\Polling\application\MessagingService;
use model\Polling\application\QueueProcessingService;
use model\Polling\application\DirectMessageProcessingService;
use model\Polling\application\QueueManagementService;
use model\Polling\domain\model\CitizenId;
use model\Polling\domain\model\filter\IFilterTemplateRepository;
use model\Polling\domain\model\sms\ICarrier;
use model\Polling\domain\model\sms\IDirectMessageRepository;
use model\Polling\domain\model\sms\IMessageTemplateRepository;
use model\Polling\domain\model\sms\Message;
use model\Polling\domain\model\sms\QueueId;
use model\Polling\domain\model\survey\Form;
use model\Polling\domain\model\survey\ISurveyLinkProvider;
use model\Polling\domain\model\survey\ISurveyRepository;
use model\Polling\domain\model\survey\ISurveySingleUseTokenGenerator;
use model\Polling\domain\model\survey\SingleUseToken;
use model\Polling\domain\model\survey\Survey;
use model\Polling\domain\model\survey\SurveyId;
use model\Polling\domain\model\survey\TokenRelation;
use model\Polling\infrastructure\FlagManager;

class MessagingTest extends TestCase {

	private DB $db;
	private FilterProvider $filters;
	private CitizenProvider $citizens;

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

		$this->filters = new FilterProvider();
		$this->citizens = new CitizenProvider();

		$this->clearDatabase();
		$this->insertCitizenData($this->citizens->males_3_females_2_with_birth_date_2000());
	}

	private function insertCitizenData(array $data) {
		foreach($data as $c)
			$this->db->insert('citizen', [
				'id' => $c['id'],
				'kimlik_no' => $c['kimlik_no'],
				'ad' => $c['ad'] ?? null,
				'soyad' => $c['soyad'] ?? null,
				'cinsiyet' => $c['cinsiyet'] ?? null,
				'telefon' => $c['telefon'] ?? null,
				'dogum_tarih' => $c['dogum_tarih'] ?? null,
				'olum_tarih' => $c['olum_tarih'] ?? null
			]);
	}

	private function clearDatabase() {
		$this->db->command("DELETE FROM citizen");
		$this->db->command("DELETE FROM citizen_queue");
		$this->db->command("DELETE FROM `message`");
		$this->db->command("DELETE FROM message_error");
		$this->db->command("DELETE FROM `queue`");
		$this->db->command("DELETE FROM queue_message");
		$this->db->command("DELETE FROM citizen_survey");
	}

	protected function setUp(): void {
		$this->clearDatabase();
	}

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

	private function carrier() {
		return $this->createMock(ICarrier::class);
	}

	private function identityProvider() {
		return $this->createMock(IIdentityProvider::class);
	}

	private function surveyRepository() {
		$surveys = $this->createMock(ISurveyRepository::class);

		$surveys->method('find')
				->willReturn(new Survey(
					new SurveyId('1'),
					'test_survey',
					new Form(
						[
							'name' => 'name',
							'description' => 'description',
							'attributes' => [
								[
									'type' => 'number',
									'required' => true,
									'name' => 'number-1'
								]
							]
						]
					),
					null,
					false
				));

		return $surveys;
	}

	private function tokenGenerator() {
		$generator = $this->createMock(ISurveySingleUseTokenGenerator::class);
		$generator->method('generateToken')
					->willReturn(new SingleUseToken(
						'tokentoken',
						new TokenRelation(
							new CitizenId('11111111111'),
							new SurveyId('1')
						),
						null
					));
		return $generator;
	}

	private function surveyLinkProvider() {
		return $this->createMock(ISurveyLinkProvider::class);
	}
	
	private function surveyValidator() : SurveyValidator {
		return $this->createMock(SurveyValidator::class);
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

	private function queueProcessingService(?ICarrier $carrier = null, ?IFlagManager $flag_manager = null) {
		return new QueueProcessingService(
			$carrier ?? $this->carrier(),
			$this->queueRepository(),
			$this->tokenGenerator(),
			$this->surveyLinkProvider(),
			$flag_manager ?? $this->flagManager(),
			null
		);
	}

	private function directMessageProcessingService(?ICarrier $carrier = null) {
		return new DirectMessageProcessingService(
			$this->directMessageRepository(),
			$carrier ?? $this->carrier(),
			$this->flagManager()
		);
	}

	private function queueManagementService(?IFlagManager $flag_manager = null) {
		return new QueueManagementService(
			$this->queueRepository(),
			$flag_manager ?? $this->flagManager()
		);
	}

	/**
	 * ?*TEST:
	 * 		birden fazla queue ile
	 * 		test edilmeli
	 */
	public function test_custom_messages_get_sent_to_filtered_citizens_successfully_and_only_once() {
		$this->clearDatabase();
		$this->insertCitizenData($this->citizens->males_3_females_2_with_birth_date_2000());

		$received = [];
		$expected = [];

		$carrier = $this->createMock(ICarrier::class);
		$carrier->expects($this->exactly(3))
				->method('send')
				->with($this->callback(function(Message $message) use (&$received) {
					$received[] = $message->recipient()->id()->getId();

					return true;
				}));

		$expected = array_values(array_map(function($item) {
			return $item['id'];
		}, array_filter($this->citizens->males_3_females_2_with_birth_date_2000(), function($item) {
			return (int)$item['cinsiyet'] === 1;
		})));

		$this->messagingService($carrier)->sendCustomMessageToCitizensUsingCustomFilter('name', $this->filters->cinsiyet(1), 'test message');

		$this->db->command("UPDATE `queue` SET created_on = '2000-01-01'"); # make sure minutes passed since query creation time is longer than 5 minutes

		$this->queueProcessingService($carrier)->processQueues(2);
		$this->queueProcessingService($carrier)->processQueues(1);
		$this->queueProcessingService($carrier)->processQueues(3);

		$this->assertEqualsCanonicalizing($expected, $received);
	}

	public function test_survey_is_successfully_attached_to_the_message() {
		$this->clearDatabase();
		$this->insertCitizenData($this->citizens->males_3_females_2_with_birth_date_2000());

		$survey = $this->surveyRepository()->find(new SurveyId(''));

		$carrier = $this->createMock(ICarrier::class);
		$carrier->expects($this->exactly(2))
				->method('send')
				->with($this->callback(function(Message $message) use (&$id_survey, &$id_no_survey) {
					if($message->id()->getId() == $id_survey)
						$this->assertSurveyTokenIsAttachedToMessage($message);
					else if($message->id()->getId() == $id_no_survey)
						$this->assertSurveyTokenIsNotAttachedToMessage($message);
					else
						throw new \Exception("Unexpected message");

					return true;
				}));

		$id_survey = $this->messagingService($carrier)->sendCustomMessageToSingleCitizen('11111111111', 'survey test', $survey->id()->getId());
		$id_no_survey = $this->messagingService($carrier)->sendCustomMessageToSingleCitizen('22222222222', 'no survey test');

		$this->directMessageProcessingService($carrier)->processDirectMessages(10);
	}

	public function test_queue_wont_process_messages_if_paused() {
		$this->clearDatabase();
		$this->insertCitizenData($this->citizens->males_3_females_2_with_birth_date_2000());

		$carrier_no_call = $this->createMock(ICarrier::class);
		$carrier_no_call->expects($this->exactly(0))
						->method('send');

		$queue_id = $this->messagingService()->sendCustomMessageToCitizensUsingCustomFilter(
			'testqueue',
			$this->filters->cinsiyet(1),
			'testmessage'
		);

		$this->db->command("UPDATE `queue` SET created_on = '2000-01-01'"); # make sure minutes passed since query creation time is longer than 5 minutes

		$this->queueManagementService()->pauseAQueue($queue_id);
		$this->queueProcessingService($carrier_no_call)->processQueues(10);
		
		$carrier_call = $this->createMock(ICarrier::class);
		$carrier_call->expects($this->exactly(3))
					->method('send');

		$this->queueManagementService()->resumeAQueue($queue_id);
		$this->queueProcessingService($carrier_call)->processQueues(10);
	}

	/**
	 * ?*TEST:
	 * 		DirectMessageProcessingService de test edilmeli
	 */
	public function test_no_message_is_sent_if_halt_message_delivery_flag_is_set() {
		$this->clearDatabase();
		$this->insertCitizenData($this->citizens->males_3_females_2_with_birth_date_2000());

		$queue_management_service = $this->queueManagementService(new FlagManager($this->db));

		$this->messagingService()->sendCustomMessageToCitizensUsingCustomFilter(
			'testqueue',
			$this->filters->cinsiyet(1),
			'testmessage'
		);

		# make sure minutes passed since query creation time is longer than 5 minutes
		$this->db->command("UPDATE `queue` SET created_on = '2000-01-01'");

		# halt message delivery
		$queue_management_service->haltMessageDelivery();
		
		# attempt to send messages and assert that carrier is never called
		$carrier_no_call = $this->createMock(ICarrier::class);
		$carrier_no_call->expects($this->exactly(0))
					->method('send');

		$this->queueProcessingService($carrier_no_call, new FlagManager(
			$this->db
		))->processQueues(10);

		# resume message delivery
		$queue_management_service->resumeMessageDelivery();

		# attempt to send messages and assert that carrier is called
		$carrier_call_once = $this->createMock(ICarrier::class);
		$carrier_call_once->expects($this->exactly(1))
					->method('send');
		
		$this->queueProcessingService($carrier_call_once, new FlagManager(
			$this->db
		))->processQueues(1);

		# halt message delivery
		$queue_management_service->haltMessageDelivery();

		# assert that carrier is never called
		$this->queueProcessingService($carrier_no_call, new FlagManager(
			$this->db
		))->processQueues(2);

		# resume message delivery
		$queue_management_service->resumeMessageDelivery();

		# attempt to send messages and assert that carrier is called
		$carrier_call_twice = $this->createMock(ICarrier::class);
		$carrier_call_twice->expects($this->exactly(2))
					->method('send');

		$this->queueProcessingService($carrier_call_twice, new FlagManager(
			$this->db
		))->processQueues(10);
	}

	private function assertSurveyTokenIsAttachedToMessage(Message $message) {
		$this->assertNotNull(
			$this->getMessage(
				$message->id()->getId()
			)['token']
		);
	}

	private function assertSurveyTokenIsNotAttachedToMessage(Message $message) {
		$this->assertNull(
			$this->getMessage(
				$message->id()->getId()
			)['token']
		);
	}

	private function getMessage(string $message_id) {
		return $this->db->query("SELECT * FROM `message` WHERE id = :id", [
			':id' => $message_id
		])->row;
	}
}

?>