<?php

use tests\Polling\data\FilterProvider;
use tests\Polling\data\CitizenProvider;

use model\Polling\infrastructure\CitizenRepository;
use model\Polling\infrastructure\FilterStatementBuilder;
use model\Polling\infrastructure\SurveyValidator;

use model\Polling\domain\model\CitizenId;
use model\Polling\domain\model\filter\IFilterResult;
use model\Polling\infrastructure\FilterResult;

class CitizenRepositoryTest extends TestCase {

	private DB $db;
	private FilterProvider $filters;
	private CitizenProvider $citizens;

	private CitizenRepository $citizen_repository;

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

		$this->citizen_repository = new CitizenRepository(
			$this->db,
			new FilterStatementBuilder(
				$this->db,
				$this->surveyValidator()
			)
		);
		
		$this->filters = new FilterProvider();
		$this->citizens = new CitizenProvider();
	}

	private function clearDatabase() {
		$this->db->command("DELETE FROM citizen");
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

	private function surveyValidator() : SurveyValidator {
		return $this->createMock(SurveyValidator::class);
	}

	private function citizenIdListFromResult(IFilterResult $result) {
		$received = [];

		while($next = $result->read())
			$received[] = $next->id()->getId();

		return $received;
	}

	/**
	 * ?*TEST:
	 * 		cok daha fazla farkli filtrenin calistigina
	 * 		emin olunmali
	 */
	public function test_correctly_returns_filtered_citizens() {
		$this->clearDatabase();
		$this->insertCitizenData($this->citizens->males_3_females_2_with_birth_date_2000());

		$filter = $this->filters->cinsiyet(1);

		$f = $this->citizen_repository->fetch($filter);

		$id_arr = $this->citizenIdListFromResult($f);

		$this->assertEqualsCanonicalizing(
			array_map(
				function($item) {
					return $item['id'];
				},
				array_filter(
					$this->citizens->males_3_females_2_with_birth_date_2000(),
					function($item) {
						return $item['cinsiyet'] == '1';
					}
				)
			),
			$id_arr
		);
	}

	public function test_wont_include_results_where_filtered_columns_value_is_null() {
		$filter = $this->filters->or(
			1,
			1,
			1,
			20,
			40500,
			24704,
			1000,
			10,
			300,
			15,
			2
		);

		$this->clearDatabase();
		$this->insertCitizenData($this->citizens->males_3_females_2_with_birth_date_2000());

		$res = $this->citizen_repository->fetch($filter);
		$id_arr = $this->citizenIdListFromResult($res);
		$this->assertCount(3, $id_arr); # number of males

		$this->clearDatabase();
		$this->insertCitizenData($this->citizens->citizens_with_null_info());

		$res = $this->citizen_repository->fetch($filter);
		$id_arr = $this->citizenIdListFromResult($res);
		$this->assertEmpty($id_arr);
	}

	public function test_find_correctly_returns_the_citizen() {
		$this->clearDatabase();
		$this->insertCitizenData($this->citizens->males_3_females_2_with_birth_date_2000());

		$this->assertEquals(
			'33333333333',
			$this->citizen_repository->find(
				new CitizenId(
					'33333333333'
				)
			)->id()->getId()
		);
	}

	public function test_date_of_death_is_used_when_calculating_age_if_citizen_is_deceased() {
		$this->clearDatabase();
		$this->insertCitizenData($this->citizens->male_deceased_at_1970_female_born_at_1985());

		$filter = $this->filters->yas_between(39, 41);
		$id_arr = $this->citizenIdListFromResult($this->citizen_repository->fetch($filter));
		$this->assertCount(1, $id_arr);

		$filter = $this->filters->yas_gt(50);
		$id_arr = $this->citizenIdListFromResult($this->citizen_repository->fetch($filter));
		$this->assertEmpty($id_arr);

		$filter = $this->filters->yas_lt(40);
		$id_arr = $this->citizenIdListFromResult($this->citizen_repository->fetch($filter));
		$this->assertCount(1, $id_arr);

		$filter = $this->filters->yas_between(35, 45);
		$id_arr = $this->citizenIdListFromResult($this->citizen_repository->fetch($filter));
		$this->assertCount(2, $id_arr);
	}
}

?>