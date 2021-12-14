<?php

namespace model\Guest\application;

use model\Guest\domain\model\IOrderRepository;
use model\common\ApplicationService;
use model\Guest\domain\model\CategoryId;
use model\Guest\domain\model\GuestId;
use model\Guest\domain\model\IGuestRepository;
use model\Guest\domain\model\IProductRepostitory;
use model\Guest\domain\model\IRoomItemRepository;
use model\Guest\domain\model\IShoppingCartRepository;
use model\Guest\domain\model\ModuleId;
use model\Guest\domain\model\ProductId;
use model\Guest\domain\model\ShoppingCart;
use model\Guest\domain\model\ShoppingCartId;
use model\Guest\domain\model\ShoppingCartItem;

class ShoppingCartManagementService extends ApplicationService{

    function __construct(private IOrderRepository $orders, private IGuestRepository $guests, private IProductRepostitory $products, private IRoomItemRepository $room_items, private IShoppingCartRepository $shopping_carts){}

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


    public function deleteSingleItemFromShoppingCart(){
        
        // $order = $this->existingOrder($order_id);

        // $order->remove();

        // $this->process($order, $this->orders);
    }

    public function addToShoppingCart(ModuleId $module_id, ?CategoryId $category_id, ProductId $product_id, float $quantity){
        
        $guest = $this->guests->find($this->guestId());

        $product = $this->products->find($product_id);

        $total_price = $product->totalPrice($quantity);

        $shopping_cart = $this->existingShoppingCart($this->guestId());

        if(!$shopping_cart){

            $id = $this->shopping_carts->nextId();

            $guest->addToShoppingCart($id, $module_id, $category_id, $product_id, $quantity, $total_price);

        }

    
        $guest->addToShoppingCart($shopping_cart['id'], $module_id, $category_id, $product_id, $quantity, $total_price);

    }

    private function existingShoppingCart(GuestId $guest_id) : ShoppingCart {
        $shopping_cart = $this->shopping_carts->find(new ShoppingCartId ($guest_id));
        if(null == $shopping_cart)
            throw new \NotFoundException('Shopping Cart is not found');

        return $shopping_cart;
    }

    protected function guestId() : GuestId {
        return new GuestId($this->identity_provider->identity());
    }
}