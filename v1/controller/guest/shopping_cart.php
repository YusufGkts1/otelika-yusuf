<?php

use model\Order\application\ShoppingCartItemManagementService;
use model\Order\application\ShoppingCartItemQueryService;

class ControllerGuestShoppingCart extends RestEndpoint{

    protected function get(){

        if(!$this->uriAt(0))
            $this->getSelfOwnedShoppingCart();
        
       $this->getSingleItemFromShoppingCart(); 
        
    }

    protected function post(){

        if(!$this->uriAt(0))
            $this->completeTheOrder();
    }

    protected function patch(){

        if($this->uriAt(0))
            $this->changeQuantity();
    }

    protected function delete(){
        
        if(!$this->uriAt(0))
            $this->emptyTheShoppingCart();
        
        $this->deleteSingleItemFromShoppingCart();
    }

    protected function submoduleId(): int{
        return 0;
    }

    protected function filterSupportingFields(): array{
        return array();
    }

    protected function orderBySupportingFields(): array{
        return $this->filterSupportingFields();
    }

    private function shoppingCartItemQueryService(): ShoppingCartItemQueryService{
        
        if ($this->shopping_cart_item_query_service)
            return $this->shopping_cart_item_query_service;

        $this->load->module('Order');

        $this->shopping_cart__item_query_service = $this->module_order->service('ShoppingCartItemQueryService');

        return $this->shopping_cart__item_query_service;
    }

    private function shoppingCartItemManagementService(): ShoppingCartItemManagementService
    {
    $this->load->module('Order');

    $this->shopping_cart_item_management_service = $this->module_order->service('ShoppingCartItemManagementService');

    return $this->shopping_cart_item_management_service;
    }

    private function getSelfOwnedShoppingCart(){

        $cart = $this->shoppingCartItemQueryService()->getSelfOwnedShoppingCart($this->queryServiceQueryObject());
        
        $this->success($cart);
    }

    private function getSingleItemFromShoppingCart(){

        $cart_item = $this->shoppingCartItemQueryService()->getSingleItemFromShoppingCart($this->uriAt(0), $this->queryServiceQueryObject());
        
        $this->success($cart_item);

    }
    private function completeTheOrder(){

        $this->ShoppingCartItemManagementService()->completeTheOrder(
            $this->getAttr('shopping_cart_id')
        );
    }

    private function changeQuantity(){

        $this->ShoppingCartItemManagementService()->changeQuantityOfShoppingCartItem(
            $this->getAttr('shopping_cart_id'),
            $this->getAttr('product_id'),
            $this->getAttr('quantity',true)
        );
    }

    private function emptyTheShoppingCart(){

        $this->ShoppingCartItemManagementService()->removeShoppingCart(
            $this->getAttr('shopping_cart_id')
        );

        $this->noContent();
    }

    private function deleteSingleItemFromShoppingCart(){

        $this->ShoppingCartItemManagementService()->deleteSingleItemFromShoppingCart(
            $this->getAttr('shopping_cart_id'),
            $this->getAttr('product_id')
        );
  
        $this->noContent();
    }


}