<?php

namespace model\Order\application;

class ShoppingCartItemQueryService extends \JsonApiQueryService {

	function __construct(
		protected \Db $db,
		private IIdentityProvider $identity_provider
	) {}

	protected function config(): array {
		return [
			'order_cart' => [
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
					'type' => 'order_cart',
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
	
	public function getSelfOwnedShoppingCart(){
        $cart = $this->db->query("SELECT * FROM `order` WHERE guest_id = :guest_id AND order_status = :order_status",[
            ':guest_id' => $this->identity_provider->identity(),
			':order_status' => OrderStatus::Pending()
        ])->rows;
        
        $result= [];

        foreach($orders as &$o){
            $format = $this->buildResource($o, 'order');
            $result[] = [
                'data' => $format
            ]; 
        }
        return $result;    
    }

	public function getSingleItemFromShoppingCart(ProductId $cart_item_id){
        $cart_item = $this->db->query("SELECT * FROM `order` WHERE guest_id = :guest_id AND product_id = :product_id AND order_status = :order_status",[
            ':guest_id' => $this->identity_provider->identity(),
			':product_id' => $cart_item_id,
			':order_status' => OrderStatus::Pending()
        ])->rows;
        
        $result= [];

            $format = $this->buildResource($cart_item, 'order');
            $result[] = [
                'data' => $format
            ]; 

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

	protected function guestId() : string {
		return $this->identity_provider->identity();
	}
}

?>