<?php

namespace model\Order\domain\model;

use DateTime;
use model\common\domain\model\Guest;
use model\common\domain\model\GuestId;
use model\common\domain\model\Product;
use model\common\domain\model\ProductId;
use model\common\domain\model\RoomId;
use model\common\Entity;

class ShoppingCart extends Entity
{
    /**
     * @param ShoppingCartItem[] $shopping_cart_items;
     */
    function __construct(
        private ShoppingCartId $id,
        private GuestId $guest_id,
        private RoomId $room_id,
        private array $shopping_cart_items,
        private float $total_price
    ){}

    //getter

    public function shoppingCartId(){
        return $this->id;
    }

    public function removeShoppingCart(){
        $this->_remove();
    }

    public function shoppingCartTotalPrice(){
        $this->total_price = 0;
        /** @var ShoppingCartItem $i */
        foreach($this->shopping_cart_items as $i){
            $this->total_price += $i->price(); 
        }
        return $this->total_price;
    }

    public function addToShoppingCart(ShoppingCartItemId $shopping_cart_item_id, Guest $guest, Product $product, float $quantity){
        /** @var ShoppingCartItem $s */
        foreach($this->shopping_cart_items as $s){

            if($s->productId() == $product->productId()){
                $s->changeQuantityOfExistCartItem($quantity);
                return;
            }
        }
        $new_shopping_cart_item = new ShoppingCartItem(
            $this->id,
            $shopping_cart_item_id,
            $guest->id(),
            $guest->roomId(),
            $product->moduleId(),
            $product->categoryId(),
            $product->productId(),
            $quantity,
            $product->totalPrice($quantity)
        ); 

        $this->shopping_cart_items[] = $new_shopping_cart_item;
    }

    public function confirmShoppingCart(OrderId $order_id, string $order_note, DateTime $delivery_time){
        /** @var ShoppingCartItem $s */
        foreach($this->shopping_cart_items as $s){

            $new_order = new Order(
                $order_id,
                $s->guestId(),
                $s->roomId(),
                $s->serviceModuleId(),
                $s->categoryId(),
                $s->productId(),
                $order_note,
                $delivery_time,
                $this->total_price
            );
            $new_orders[] = $new_order;
        }
        return $new_orders;
    }

  

  
}
