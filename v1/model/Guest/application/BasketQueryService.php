<?php

namespace model\Guest\application;

class BasketQueryService extends \JsonApiQueryService {

	function __construct(
		protected \Db $db,
		private IIdentityProvider $identity_provider
	) {}

	protected function config(): array {
		return [
			'order_basket' => [
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
								'guest_id' => 'guestId'
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
					'type' => 'order_basket',
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

	public function fetchGuestSelfOwnedBasket(){
        $order_basket = $this->db->query("SELECT * FROM `order_basket` WHERE guest_id = :guest_id", [
            ':guest_id'=>$this->identity_provider->identity(),
        ])->rows;
        
        $result= [];

        foreach($order_basket as &$o){
            $format = $this->buildResource($o, 'order_basket');
            $result[] = [
                'data' => $format
            ]; 
        }
        return $result;    
    }

    public function getGuestSingleBasketItemById($guest_id){

        $order_basket_item = $this->db->query("SELECT * FROM `order_basket` WHERE id = :id ", [
            ':id' => $guest_id
        ])->row;

        $format = $this->buildResource($order_basket_item, 'order_basket');

        return $format;
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

	protected function guestId() : string {
		return $this->identity_provider->identity();
	}
}

?>