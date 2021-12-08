<?php

namespace model\Guest\application;

use model\Guest\application\IIdentityProvider;

class BasketQueryService extends \JsonApiQueryService {

	function __construct(
		protected \DB $db,
		private IIdentityProvider $identity_provider
	) {}

	protected function config(): array {
		return [
            'profile' => [
				'table' => 'student',
				'single' => [
					'by' => [
						null => [
							'nullable' => false,
							'provider' => [
								'id' => 'studentId'
							]
						]
					]
				],
				'json_api' => [
					'id_field' => 'id',
					'type' => 'student',
					'translate' => [
						// 'date_added' => [
						// 	'translator' => 'dateISO8601',
						// 	'replace' => true
						// ]
					],
					'exclude' => [
						'is_active'
					]
				]
			]
		];
	}

	protected function dateISO8601($date) {
		if($date)
			return (new \DateTime($date))->format(DATE_ISO8601);
		else
			return null;
	}

	protected function studentId() : string {
		return $this->identity_provider->identity();
	}

	protected function db(): \DB {
		return $this->db;
	}
}

?>