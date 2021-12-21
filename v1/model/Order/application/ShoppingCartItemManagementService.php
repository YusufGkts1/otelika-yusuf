<?php

namespace model\Order\application;

use DateTime;
use model\common\ApplicationService;
use model\common\domain\model\GuestId;
use model\common\domain\model\IGuestRepository;
use model\common\domain\model\IProductRepostitory;
use model\common\domain\model\IServiceModuleRepository;
use model\common\domain\model\Product;
use model\common\domain\model\ProductId;
use model\common\domain\model\ServiceModuleId;
use model\Order\domain\model\CategoryId;
use model\Order\domain\model\ICategoryRepository;
use model\Order\domain\model\IShoppingCartItemRepository;
use model\Order\domain\model\IShoppingCartRepository;
use model\Order\domain\model\ShoppingCartId;
use model\Order\domain\model\ShoppingCartItem;
use model\Order\domain\model\ShoppingCartItemId;

class ShoppingCartItemManagementService extends ApplicationService{

    function __construct(
        private IGuestRepository $guests,
        private IProductRepostitory $products,
        private IShoppingCartItemRepository $shopping_cart_items,
        private IShoppingCartRepository $shopping_carts,
        private IServiceModuleRepository $service_modules,
        private ICategoryRepository $categories){}

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


    public function completeTheOrder(ShoppingCartItemId $shopping_cart_item_id){

        $shopping_cart_item = $this->shopping_cart_items->find($shopping_cart_item_id);

        if(!$shopping_cart_item)
            throw new \NotFoundException('Shopping Cart is not found');

        $order_id = $this->orders->nextId();
         
        $new_order = $shopping_cart_item->addToOrders($order_id);

        $this->process($new_order, $this->orders);
    }

    public function addToShoppingCart(ProductId $product_id, $quantity){

        $product = $this->existingProduct($product_id);

        $guest = $this->guests->find($this->guestId());

        $shopping_cart = $this->shopping_carts->getShoppingCartByGuestId($this->guestId());

        $shopping_cart_item_id = $this->shopping_cart_items->nextId();

        $shopping_cart->addToShoppingCart($shopping_cart_item_id, $guest, $product, $quantity);

    }

    // public function addToShoppingCart(ServiceModuleId $module_id, ?CategoryId $category_id, ProductId $product_id, ?string $order_note, ?DateTime $delivery_time, float $quantity){
        

    //     $total_price = $product->totalPrice($quantity);

    //     $shopping_cart = $this->existingShoppingCart($this->guestId());

    //     if(!$shopping_cart){

    //         $id = $this->shopping_carts->nextId();

    //         $guest->addToShoppingCart($id, $module_id, $category_id, $product_id, $order_note, $delivery_time, $quantity, $total_price);
    //     }

    //     $guest->addToShoppingCart($shopping_cart['id'], $module_id, $category_id, $product_id, $order_note, $delivery_time, $quantity, $total_price);
    // }

    public function removeShoppingCart(ShoppingCartId $shopping_cart_id){

        $shopping_cart = $this->shopping_carts->find($shopping_cart_id);

        if(!$shopping_cart)
            throw new \NotFoundException('Shopping Cart is not found');

        $shopping_cart->removeShoppingCart();

        $this->process($shopping_cart, $this->shopping_carts);

    }

    public function deleteSingleItemFromShoppingCart(ShoppingCartItemId $shopping_cart_item_id){

        $shopping_cart_item = $this->existingShoppingCartItem($shopping_cart_item_id);

        $shopping_cart_item->removeShoppingCartItem($shopping_cart_item_id);
        
    }

    public function changeQuantityOfShoppingCartItem(ShoppingCartItemId $shopping_cart_id, ProductId $product_id, float $quantity){

        $shopping_cart_item = $this->existingShoppingCartItem($shopping_cart_id, $product_id);

        $product = $this->products->find($product_id);

        $total_price = $product->totalPrice($quantity);

        $shopping_cart_item->newTotalPrice($total_price);

        $shopping_cart_item->changeQuantityOfCartItem($quantity);

        $this->process($shopping_cart_item, $this->shopping_cart_items);

    }

    private function existingShoppingCartId(GuestId $guest_id) : ShoppingCartId {
        $shopping_cart_id = $this->shopping_carts->getShoppingCartIdByGuestId($guest_id);

        return $shopping_cart_id;
    }

    private function existingShoppingCartItem(ShoppingCartItemId $shopping_cart_id) : ShoppingCartItem {

        $shopping_cart_item = $this->shopping_cart_items->find($shopping_cart_id);
        if(null == $shopping_cart_item)
            throw new \NotFoundException('Shopping Cart Item is not found');

        return $shopping_cart_item;
    }

    private function existingProduct(ProductId $product_id) : Product{

        $product = $this->products->find($product_id);
        if(null == $product)
            throw new \NotFoundException('Product is not found');

        return $product;
    }

    protected function guestId() : GuestId {
        return new GuestId($this->identity_provider->identity());
    }
}