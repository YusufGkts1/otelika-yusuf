<?php

namespace model\Order\application;

use DateTime;
use model\common\ApplicationService;
use model\common\domain\model\GuestId;
use model\common\domain\model\IGuestRepository;
use model\common\domain\model\IProductRepository;
use model\common\domain\model\IServiceModuleRepository;
use model\common\domain\model\Product;
use model\common\domain\model\ProductId;
use model\Order\domain\model\ICategoryRepository;
use model\Order\domain\model\IShoppingCartItemRepository;
use model\Order\domain\model\IShoppingCartRepository;
use model\Order\domain\model\ShoppingCartId;
use model\Order\domain\model\ShoppingCartItem;
use model\Order\domain\model\ShoppingCartItemId;

class ShoppingCartItemManagementService extends ApplicationService{

    function __construct(
        private IGuestRepository $guests,
        private IProductRepository $products,
        private IShoppingCartItemRepository $shopping_cart_items,
        private IShoppingCartRepository $shopping_carts,
        private IServiceModuleRepository $service_modules,
        private ICategoryRepository $categories){}

    public function completeTheOrder(ShoppingCartId $shopping_cart_id, ?string $order_note, DateTime $delivery_time){

        $shopping_cart = $this->shopping_carts->find($shopping_cart_id);

        if(!$shopping_cart)
            throw new \NotFoundException('Shopping Cart is not found');

        $order_id = $this->orders->nextId();
         
        $new_orders = $shopping_cart->confirmShoppingCart($order_id, $order_note, $delivery_time);

        foreach($new_orders as $o){

            $this->process($o, $this->orders);
        }
    }

    public function addToShoppingCart(ProductId $product_id, $quantity){

        $product = $this->existingProduct($product_id);

        $guest = $this->guests->find($this->guestId());

        $shopping_cart = $this->shopping_carts->getShoppingCartByGuestId($this->guestId());

        $shopping_cart_item_id = $this->shopping_cart_items->nextId();

        $shopping_cart->addToShoppingCart($shopping_cart_item_id, $guest, $product, $quantity);

    }

    public function removeShoppingCart(ShoppingCartId $shopping_cart_id){

        $shopping_cart = $this->shopping_carts->find($shopping_cart_id);

        if(!$shopping_cart)
            throw new \NotFoundException('Shopping Cart is not found');

        $shopping_cart->removeShoppingCart();

        $this->process($shopping_cart, $this->shopping_carts);

    }

    public function deleteSingleItemFromShoppingCart(ShoppingCartItemId $shopping_cart_item_id){

        $shopping_cart_item = $this->existingShoppingCartItem($shopping_cart_item_id);

        $shopping_cart_item->removeShoppingCartItem();
        
    }

    public function changeQuantityOfShoppingCartItem(ShoppingCartItemId $shopping_cart_id, ProductId $product_id, float $quantity){

        $shopping_cart_item = $this->existingShoppingCartItem($shopping_cart_id, $product_id);

        $product = $this->products->find($product_id);

        $total_price = $product->totalPrice($quantity);

        $shopping_cart_item->newTotalPrice($total_price);

        $shopping_cart_item->changeQuantityOfCartItem($quantity);

        $this->process($shopping_cart_item, $this->shopping_cart_items);

    }

    private function existingShoppingCartItem(ShoppingCartItemId $shopping_cart_item_id) : ShoppingCartItem {

        $shopping_cart_item = $this->shopping_cart_items->find($shopping_cart_item_id);
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