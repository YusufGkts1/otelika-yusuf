<?php

namespace model\Guest\domain\model;

use model\common\Entity;

class ShoppingCartItem extends Entity
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

    public function changeQuantityOfcartItem(float $quantity){

        $total_price = $quantity * $this->price;

        // Product.php içerisinde mi yapmalı bunu?
    }

    public function removeShoppingCartItem(){
        $this->_remove();
    }
}
