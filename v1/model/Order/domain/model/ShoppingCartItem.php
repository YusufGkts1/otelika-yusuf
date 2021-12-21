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
        private ShoppingCartId $shopping_cart_id,
        private ShoppingCartItemId $id,
        private GuestId $guest_id,
        private RoomId $room_id,
        private ServiceModuleId $module_id,
        private ?CategoryId $category_id,
        private ProductId $product_id,
        private float $quantity,
        private float $total_price
    ){}

    public function addToOrders(OrderId $order_id){

        return new Order(
            $order_id,
            $this->guest_id,
            $this->room_id,
            $this->module,
            $this->product_id,
            $this->order_note,
            $this->delivery_time,
            $this->total_price
        );
    }

    public function newTotalPrice($total_price){

        $this->total_price = $total_price;

        return $this->total_price;
    }

    public function changeQuantityOfCartItem(float $quantity){

        $this->quantity = $quantity;

        return $this->quantity;
    }

    public function removeShoppingCartItem(){}
}
