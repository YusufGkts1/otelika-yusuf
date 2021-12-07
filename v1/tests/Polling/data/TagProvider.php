<?php

namespace tests\Polling\data;

class TagProvider {

	public function cinsiyet(int $value) {
		return json_decode('{
			"type": "parentFilter",
			"children": [
				{
					"type": "filter",
					"filter": {
						"property": "cinsiyet",
						"operator": "eq",
						"value": ' . $value . '
					}
				}
			]
		}', true);
	}
	
	public function yas_lt(int $value) {
		return json_decode('{
			"type": "parentFilter",
			"children": [
				{
					"type": "filter",
					"filter": {
						"property": "yas",
						"operator": "lt",
						"value": ' . $value . '
					}
				}
			]
		}', true);
	}

	public function yas_gt(int $value) {
		return json_decode('{
			"type": "parentFilter",
			"children": [
				{
					"type": "filter",
					"filter": {
						"property": "yas",
						"operator": "gt",
						"value": ' . $value . '
					}
				}
			]
		}', true);
	}

	public function yas_between(int $min, int $max) {
		return json_decode('{
			"type": "parentFilter",
			"children": [
				{
					"type": "filter",
					"filter": {
						"property": "yas",
						"operator": "gt",
						"value": ' . $min . '
					}
				},
				{
					"type": "connector",
					"connector": {
						"connector_value": "and"
					}
				},
				{
					"type": "filter",
					"filter": {
						"property": "yas",
						"operator": "lt",
						"value": ' . $max . '
					}
				}
			]
		}', true);
	}
	
	public function and(
		$medeni_hal=null,
		$durum=null,
		$cinsiyet=null,
		$yas=null,
		$mahalle_kodu=null,
		$csbm_kodu=null,
		$bina_no=null,
		$ada=null,
		$parsel=null,
		$dis_kapi_no=null,
		$ic_kapi_no=null
	) {
		$args = get_defined_vars();

		$filters = [
			'type' => 'parentFilter',
			'children' => [

			]
		];

		foreach($args as $arg => $val) {
			if($val) { # not null
				if($filters['children']) # add connector
					$filters['children'][] = [
						'type' => 'connector',
						'connector' => [
							'connector_value' => 'and'
						]
					];
					
				$filters['children'][] = [
					'type' => 'filter',
					'filter' => [
						'property' => $arg,
						'operator' => 'eq',
						'value' => $val
					]
				];
			}
		}

		return $filters;
	}

	public function or(
		$medeni_hal=null,
		$durum=null,
		$cinsiyet=null,
		$yas=null,
		$mahalle_kodu=null,
		$csbm_kodu=null,
		$bina_no=null,
		$ada=null,
		$parsel=null,
		$dis_kapi_no=null,
		$ic_kapi_no=null
	) {
		$args = get_defined_vars();

		$filters = [
			'type' => 'parentFilter',
			'children' => [

			]
		];

		foreach($args as $arg => $val) {
			if($val) { # not null
				if($filters['children']) # add connector
					$filters['children'][] = [
						'type' => 'connector',
						'connector' => [
							'connector_value' => 'or'
						]
					];

				$filters['children'][] = [
					'type' => 'filter',
					'filter' => [
						'property' => $arg,
						'operator' => 'eq',
						'value' => $val
					]
				];
			}
		}

		return $filters;
	}
}

?>