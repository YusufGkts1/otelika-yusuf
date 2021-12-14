<?php

namespace model\Guest\application;

use model\Guest\domain\model\ProductType;

class ProductQueryService extends \JsonApiQueryService {

	function __construct(
		protected \Db $db,
	) {}

	protected function config(): array {
		return [
			'product' => [
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
							'nullable' => true,
							'provider' => [
								'module_id' => 'module_id'
							]						]
					],
					'filter' => [
						'created_on', 'updated_on'
					],
					'sort' => [
						'created_on', 'updated_on'
					]
				],
				'json_api' => [
					'id_field' => 'id',
					'type' => 'product',
					'translate' => [
						'created_on' => [
							'translator' => 'dateISO8601',
							'replace' => true
						],
						'updated_on' => [
							'translator' => 'dateISO8601',
							'replace' => true
						]
					]
				]
			]
		];
	}

	public function fetchFaultRecordProducts(){
        $products = $this->db->query("SELECT * FROM `product` WHERE product_type = :product_type", [
            ':product_type'=> ProductType::FaultRecord()
        ])->rows;
        
        $result= [];

        foreach($products as &$p){
            $format = $this->buildResource($p, 'product');
            $result[] = [
                'data' => $format
            ]; 
        }
        return $result;    
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