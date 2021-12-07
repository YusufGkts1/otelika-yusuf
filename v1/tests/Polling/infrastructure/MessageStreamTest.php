<?php

use model\Polling\infrastructure\MessageStream;

use model\Polling\domain\model\sms\IQueueRepository;
use model\Polling\domain\model\filter\IFilterResult;
use model\Polling\domain\model\Citizen;
use model\Polling\domain\model\CitizenId;
use model\Polling\domain\model\tag\TagList;

class MessageStreamTest extends TestCase {

	private int $citizen_id_counter = 1;

	function __construct() {
		global $framework;

		parent::__construct($framework);
	}

	protected function setUp(): void {
		$this->citizen_id_counter = 1;
	}

	private function queueRepository() {
		$queue_repository = $this->createMock(IQueueRepository::class);

		return $queue_repository;
	}

	private function genericFilterResult(?int $length = null) {
		$filter_result = $this->createMock(IFilterResult::class);
		$filter_result->method('read')->will($this->returnCallback(function() use ($length) {
			if($length && $this->citizen_id_counter > $length)
				return null;

			$c = $this->citizen_id_counter;

			$this->citizen_id_counter += 1;

			return new Citizen(
				new CitizenId($c),
				'111',
				'firstname ' . $c,
				'lastname ' . $c,
				'phone ' . $c,
				null,
				null,
				null,
				null,
				null,
				null,
				null,
				null,
				null,
				new TagList([])
			);
		}));

		return $filter_result;
	}

	private function genericMessageStream(?int $length = null) {
		return new MessageStream(
			$this->genericFilterResult($length),
			'message content',
			$this->queueRepository()
		);
	}

	public function test_message_stream_returns_a_message() {
		$stream = $this->genericMessageStream();

		$msg = $stream->pop(1);

		$this->assertNotNull($msg);
	}

	public function test_returns_just_as_many_messages_as_requested() {
		$stream = $this->genericMessageStream();

		$msgs = $stream->pop(2);

		$this->assertEquals(2, count($msgs));

		$msgs = $stream->pop(3);
		
		$this->assertEquals(3, count($msgs));

		$msgs = $stream->pop(1);
		
		$this->assertEquals(1, count($msgs));
	}

	public function test_wont_return_duplicate_recipients() {
		$stream = $this->genericMessageStream();

		$msgs = $stream->pop(10);
		$msgs = array_merge($msgs, $stream->pop(10));
		$msgs = array_merge($msgs, $stream->pop(10));

		$recipients = [];

		foreach($msgs as $m) {
			$recipient_id = $this->getProperty($m->recipient(), 'id')->getId();

			$this->assertTrue(!in_array(
				$recipient_id,
				$recipients 
			));

			$recipients[] = $recipient_id;
		}
	}

	public function test_returns_empty_array_when_depleted() {
		$stream = $this->genericMessageStream(5);

		$msgs = $stream->pop(5);

		$this->assertEquals(count($msgs), 5);

		$msgs = $stream->pop(5);

		$this->assertEmpty($msgs);
	}
}

?>