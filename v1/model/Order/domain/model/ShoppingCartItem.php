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

    // getter
    public function guestId(){
        return $this->guest_id;
    }

    public function roomId(){
        return $this->room_id;
    }

    public function serviceModuleId(){
        return $this->module_id;
    }

    public function categoryId(){
        return $this->category_id;

    }

    public function productId(){
        return $this->product_id;
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

    public function changeQuantityOfExistCartItem(float $quantity){

        $this->quantity = $this->quantity + $quantity;

        return $this->quantity;
    }
}
