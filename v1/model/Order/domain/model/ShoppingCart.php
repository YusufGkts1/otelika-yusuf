<?php

namespace model\Order\domain\model;

use model\common\domain\model\GuestId;
use model\common\domain\model\RoomId;
use model\common\Entity;

class ShoppingCart extends Entity
{
    function __construct(
        private ShoppingCartId $id,
        private GuestId $guest_id,
        private RoomId $room_id,
        private ?string $order_note,
        private ?\DateTime $delivery_time,
        private float $total_price
    ){}

    //getter

    public function shoppingCartId(){
        return $this->id;
    }

    public function removeShoppingCart(){
        $this->_remove();
    }
  
}
