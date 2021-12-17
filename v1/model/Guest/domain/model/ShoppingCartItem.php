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

    public function changeQuantityOfcartItem(ProductId $cart_item_id, float $quantity){

    
    }

    public function removeShoppingCartItem(){
        $this->_remove();
    }
}
