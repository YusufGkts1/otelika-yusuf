<?php

use model\Polling\domain\model\survey\ISurveySingleUseTokenGenerator;

use model\Polling\domain\model\sms\ICarrier;
use model\Polling\domain\model\sms\IMessageList;
use model\Polling\domain\model\sms\MessagePreprocessor;
use model\Polling\domain\model\sms\Queue as SmsQueue;
use model\Polling\domain\model\sms\QueueId;

class QueueTest extends TestCase {

	function __construct() {
		global $framework;

		parent::__construct($framework);
	}

	private function carrier() {
		return $this->createMock(ICarrier::class);
	}

	private function messagePreprocessor() {
		return $this->createMock(MessagePreprocessor::class);
	}

	private function tokenGenerator() {
		return $this->createMock(ISurveySingleUseTokenGenerator::class);
	}

	private function genericMessageList() : IMessageList {
		return $this->createMock(IMessageList::class);
	}

	private function genericQueue(?QueueId $queue_id = null, ?IMessageList $message_list = null, bool $is_paused = false, ?\DateTime $created_on = null) : SmsQueue {
		return new SmsQueue(
			$queue_id ?? new QueueId('1'),
			'name',
			$message_list ?? $this->genericMessageList(),
			$is_paused,
			null,
			$created_on ?? new \DateTime('now')
		);
	}

	public function test_wont_process_if_created_in_the_last_5_minutes() {
		$message_list = $this->createMock(IMessageList::class);
		$message_list->expects($this->never())
						->method('pop');

		$queue = $this->genericQueue(
			message_list: $message_list,
			created_on: DateTime::createFromFormat('U', time()) # now
		);

		$queue->process(
			$this->carrier(),
			1,
			$this->messagePreprocessor(),
			$this->tokenGenerator()
		);
	}

	public function test_will_process_if_more_than_5_minutes_passed_since_creation() {
		$message_list = $this->createMock(IMessageList::class);
		$message_list->expects($this->once())
						->method('pop');

		$queue = $this->genericQueue(
			message_list: $message_list,
			created_on: DateTime::createFromFormat('U', time() - 6 * 60) # six minutes ago
		);

		$queue->process(
			$this->carrier(),
			1,
			$this->messagePreprocessor(),
			$this->tokenGenerator()
		);
	}

	public function test_wont_process_if_paused() {
		$message_list = $this->createMock(IMessageList::class);
		$message_list->expects($this->never())
						->method('pop');

		$queue = $this->genericQueue(
			message_list: $message_list,
			is_paused: true
		);

		$queue->process(
			$this->carrier(),
			1,
			$this->messagePreprocessor(),
			$this->tokenGenerator()
		);
	}

	public function test_will_process_if_resumed() {
		$message_list = $this->createMock(IMessageList::class);
		$message_list->expects($this->once())
						->method('pop');

		$queue = $this->genericQueue(
			message_list: $message_list,
			is_paused: true,
			created_on: DateTime::createFromFormat('U', time() - 6 * 60) # six minutes ago
		);

		$queue->resume();

		$queue->process(
			$this->carrier(),
			1,
			$this->messagePreprocessor(),
			$this->tokenGenerator()
		);
	}
}

?>