<?php

namespace model\Guest\domain\model;

use DateTime;
use model\common\Entity;

class ShoppingCart extends Entity
{
    function __construct(
        private ShoppingCartId $id,
        private GuestId $guest_id,
        private RoomId $room_id,
        private ModuleId $module_id,
        private ?CategoryId $category_id,
        private ProductId $product_id,
        private string $order_note,
        private ?\DateTime $delivery_time,
        private float $quantity,
        private float $total_price
    ){}

    public function changeQuantityOfcartItem(ProductId $cart_item_id, float $quantity){

        


    }

    public function remove() {
		$this->_remove();
	}

    public function addToOrders(OrderId $order_id){
        return new Order(
            $order_id,
            $this->guest_id,
            $this->room_id,
            $this->module_id,
            $this->product_id,
            $this->order_note,
            $this->delivery_time,
            $this->total_price    
        );
    }
}
