<?php

namespace model\Order\domain\model;

use model\common\domain\model\GuestId;
use model\common\domain\model\ProductId;
use model\common\domain\model\RoomId;
use model\common\domain\model\ServiceModuleId;
use model\common\Entity;

class ShoppingCartItem extends Entity
{
    function __construct(
        private ShoppingCartItemId $id,
        private GuestId $guest_id,
        private RoomId $room_id,
        private ServiceModuleId $module_id,
        private ?CategoryId $category_id,
        private ProductId $product_id,
        private string $order_note,
        private ?\DateTime $delivery_time,
        private float $quantity,
        private float $total_price
    ){}

    public function changeQuantityOfCartItem(float $quantity){

        $total_price = $quantity * $this->price;

        // Product.php içerisinde mi yapmalı bunu?
    }

    public function removeShoppingCartItem(){
        $this->_remove();
    }
}
