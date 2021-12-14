<?php

namespace model\Guest\application;

use model\Guest\domain\model\IOrderRepository;
use model\common\ApplicationService;
use model\Guest\domain\model\GuestId;
use model\Guest\domain\model\IGuestRepository;
use model\Guest\domain\model\IRoomItemRepository;
use model\Guest\domain\model\IShoppingCartRepository;
use model\Guest\domain\model\ModuleId;
use model\Guest\domain\model\ProductId;

class ShoppingCartManagementService extends ApplicationService{

    function __construct(private IOrderRepository $orders, private IGuestRepository $guests, private IRoomItemRepository $room_items, private IShoppingCartRepository $shopping_carts){}

    // public function deleteItem(ProductId $cart_item_id){

    //     $cart_item = $this->existingCartItem($cart_item_id);

    //     $cart_item->remove();

    //     $this->process($cart_item, $this->cart_items);
    // }

    public function changeQuantityOfProduct(ProductId $cart_item_id, float $quantity){

        $shopping_cart = $this->shopping_carts->findCartByGuestId($this->guestId());

        if(!$shopping_cart)
            throw new \NotFoundException('Cart is not found.');

        $shopping_cart->changeQuantityOfCartItem($cart_item_id, $quantity);
        
    }

//     private function existingProductInCart(ProductId $cart_item_id) : Product {
//         $Cart_item = $this->Carts->findProductInCart(new ProductId ($Cart_item_id));

//         if(null == $Cart_item)
//            throw new \NotFoundException('Product is not found');

//        return $Cart_item;
//    }


public function deleteSingleItemFromShoppingCart(OrderId $order_id){
        
    $order = $this->existingOrder($order_id);

    $order->remove();

    $this->process($order, $this->orders);
    

}

public function addToShoppingCart(ModuleId $module_id, ProductId $product_id, float $quantity, float $price){
        
    $id = $this->shopping_carts->nextId();

    $guest = $this->guests->find($this->guestId());

    $total_price = $quantity * $price;
    
    $guest->addToShoppingCart($id, $module_id, $product_id, $quantity, $total_price);

}

    protected function guestId() : GuestId {
        return new GuestId($this->identity_provider->identity());
    }
}