<?php

namespace model\Guest\application;

use model\Guest\application\IIdentityProvider;

class ModuleQueryService extends \JsonApiQueryService {

	function __construct(
		protected \DB $db
	) {}

	protected function config(): array {
		return [
			'module' => [
				'single' => [
					'by' => [
						'id' => [
							'nullable' => false
						]
					]
				],
				'multiple' => [
					'by' => [
						null => [
							'nullable' => true
						]
					],
					'filter' => [
						'created_on', 'last_used_on', 'updated_on'
					],
					'sort' => [
						'created_on', 'last_used_on', 'updated_on'
					]
				],
				'json_api' => [
					'id_field' => 'id',
					'type' => 'module',
					'translate' => [
						'created_on' => [
							'translator' => 'dateISO8601',
							'replace' => true
						],
						'last_used_on' => [
							'translator' => 'dateISO8601',
							'replace' => true
						],
						'updated_on' => [
							'translator' => 'dateISO8601',
							'replace' => true
						]
					]
				],
				'processor' => 'filterTemplateProcessor'
			]
		];
	}

	protected function dateISO8601($date) {
		if($date)
			return (new \DateTime($date))->format(DATE_ISO8601);
		else
			return null;
	}

	protected function db(): \DB {
		return $this->db;
	}
}

?>