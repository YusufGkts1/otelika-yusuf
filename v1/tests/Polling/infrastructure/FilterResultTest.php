<?php

use model\Polling\domain\model\Citizen;
use model\Polling\domain\model\CitizenId;
use model\Polling\domain\model\ICitizenRepository;
use model\Polling\domain\model\tag\TagList;
use model\Polling\infrastructure\CitizenRepository;
use model\Polling\infrastructure\CitizenStream;
use model\Polling\infrastructure\FilterResult;

class FilterResultTest extends TestCase {

	function __construct() {
		global $framework;

		parent::__construct($framework);
	}

	private function sampleCitizenStatusList() {
		return [
			'1' => true,
			'2' => false,
			'3' => true,
			'4' => false,
			'5' => false,
			'6' => false,
			'7' => true,
			'8' => true,
			'9' => false
		];
	}

	private function citizenRepository() {
		$citizen_repository = $this->createMock(ICitizenRepository::class);
		
		$citizen_repository->method('find')->will($this->returnCallback(function(CitizenId $id) {
			return new Citizen(
				$id,
				'123123',
				'firstname',
				'lastname',
				'phone',
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

		return $citizen_repository;
	}

	private function genericFilterResult() {
		return new FilterResult(
			$this->sampleCitizenStatusList(),
			$this->citizenRepository()
		);
	}

	public function test_returns_length_correctly() {
		$r = $this->genericFilterResult();

		$this->assertEquals(count($this->sampleCitizenStatusList()), $r->length());
	}

	public function test_returns_citizen() {
		$r = $this->genericFilterResult();

		$this->assertNotNull($r->read());
	}

	public function test_wont_return_same_citizen_more_than_once() {
		$r = $this->genericFilterResult();

		$received = [];

		while($next = $r->read()) {
			$this->assertTrue(!in_array($next->id()->getId(), $received));

			$received[] = $next->id()->getId();
		}
	}

	public function test_returns_every_unprocessed_citizen() {
		$r = $this->genericFilterResult();

		$received = [];

		while($next = $r->read())
			$received[] = $next->id()->getId();

		$this->assertEqualsCanonicalizing(
			array_keys(
				array_filter(
					$this->sampleCitizenStatusList(),
					function($item) {
						return !$item;
					}
				)
			),
			$received
		);
	}

	public function test_returns_null_when_depleted() {
		$r = $this->genericFilterResult();

		# get unprocessed citizens
		$queued = array_filter($this->sampleCitizenStatusList(), function($item) {
			return !$item;
		});

		# deplete the queue
		for($i = 0; $i < count($queued); $i++)
			$this->assertNotNull($r->read());

		$this->assertNull($r->read());
	}
}

?>