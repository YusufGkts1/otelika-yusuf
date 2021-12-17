<?php

namespace model\Guest\application;

use model\Guest\domain\model\IOrderRepository;
use model\common\ApplicationService;
use model\Guest\domain\model\CategoryId;
use model\Guest\domain\model\GuestId;
use model\Guest\domain\model\ICategoryRepository;
use model\Guest\domain\model\IGuestRepository;
use model\Guest\domain\model\IModuleRepository;
use model\Guest\domain\model\IProductRepostitory;
use model\Guest\domain\model\IRoomItemRepository;
use model\Guest\domain\model\IShoppingCartRepository;
use model\Guest\domain\model\ModuleId;
use model\Guest\domain\model\Product;
use model\Guest\domain\model\ProductId;
use model\Guest\domain\model\ShoppingCart;
use model\Guest\domain\model\ShoppingCartId;
use model\Guest\domain\model\ShoppingCartItem;

class ShoppingCartManagementService extends ApplicationService{

    function __construct(
        private IOrderRepository $orders,
        private IGuestRepository $guests,
        private IProductRepostitory $products,
        private IRoomItemRepository $room_items,
        private IShoppingCartRepository $shopping_carts,
        private IModuleRepository $modules,
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


    public function completeTheOrder(ShoppingCartId $shopping_cart_id){

        $shopping_cart = $this->shopping_carts->find($shopping_cart_id);

        if(!$shopping_cart)
            throw new \NotFoundException('Shopping Cart is not found');

        $order_id = $this->orders->nextId();
         
        $new_order = $shopping_cart->addToOrders($order_id);

        $this->process($new_order, $this->orders);
    }

    public function addToShoppingCart(ModuleId $module_id, ?CategoryId $category_id, ProductId $product_id, float $quantity){
        
        $guest = $this->guests->find($this->guestId());

        $module = $this->modules->find($module_id);

        if(!$module)
            throw new \NotFoundException('Module is not found');

        $category = $this->categories->find($category_id);

        if(!$category)
            throw new \NotFoundException('Category is not found');    

        $product = $this->products->find($product_id);

        if(!$product)
            throw new \NotFoundException('Product is not found');

        $total_price = $product->totalPrice($quantity);

        $shopping_cart = $this->existingShoppingCart($this->guestId());

        if(!$shopping_cart){

            $id = $this->shopping_carts->nextId();

            $guest->addToShoppingCart($id, $module_id, $category_id, $product_id, $quantity, $total_price);
        }

        $guest->addToShoppingCart($shopping_cart['id'], $module_id, $category_id, $product_id, $quantity, $total_price);
    }

    public function removeShoppingCart(ShoppingCartId $shopping_cart_id){

        $shopping_cart = $this->shopping_carts->find($shopping_cart_id);

        if(!$shopping_cart)
            throw new \NotFoundException('Shopping Cart is not found');

        $shopping_cart->remove();

        $this->process($shopping_cart, $this->shopping_carts);

    }

    public function deleteSingleItemFromShoppingCart(ShoppingCartId $shopping_cart_id, ProductId $product_id){

        $shopping_cart_item = $this->existingShoppingCartItem($shopping_cart_id, $product_id);

        $shopping_cart_item->removeShoppingCartItem($shopping_cart_id, $product_id);
        
    }

    public function changeQuantityOfShoppingCartItem(ShoppingCartId $shopping_cart_id, ProductId $product_id, float $quantity){

        $shopping_cart_item = $this->existingShoppingCartItem($shopping_cart_id, $product_id);

        $shopping_cart_item->changeQuantityOfcartItem($product_id, $quantity);

        $this->process($shopping_cart_item, $this->shopping_cart_item);

    }

     //Shopping Cart Item çekilerek ShoppingItem.php içerisinde change function ile işlem tamamlanacak.

    private function existingShoppingCart(GuestId $guest_id) : ShoppingCart {
        $shopping_cart = $this->shopping_carts->find(new ShoppingCartId ($guest_id));

        return $shopping_cart;
    }

    private function existingShoppingCartItem(ShoppingCartId $shopping_cart_id, ProductId $product_id) : ShoppingCartItem {

        $shopping_cart_item = $this->shopping_carts->findShoppingCartItem($shopping_cart_id, $product_id);
        if(null == $shopping_cart_item)
            throw new \NotFoundException('Shopping Cart Item is not found');

        return $shopping_cart_item;
    }

    protected function guestId() : GuestId {
        return new GuestId($this->identity_provider->identity());
    }
}