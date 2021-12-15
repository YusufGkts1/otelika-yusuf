<?php

use model\Guest\application\OrderManagementService;
use model\Guest\application\OrderQueryService;
use model\Guest\application\ShoppingCartManagementService;
use model\Guest\application\ShoppingCartQueryService;

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

    private function shoppingCartQueryService(): ShoppingCartQueryService{
        if ($this->shopping_cart_query_service)
            return $this->shopping_cart_query_service;

        $this->load->module('Guest');

        $this->shopping_cart_query_service = $this->module_guest->service('ShoppingCartQueryService');

        return $this->shopping_cart_query_service;
    }

    private function shoppingCartManagementService(): ShoppingCartManagementService{
    $this->load->module('Guest');

    $this->shopping_cart_management_service = $this->module_guest->service('ShoppingCartManagementService');

    return $this->shopping_cart_management_service;
    }

    private function getSelfOwnedShoppingCart(){

        $cart = $this->shoppingCartQueryService()->getSelfOwnedShoppingCart($this->queryServiceQueryObject());
        
        $this->success($cart);
    }

    private function getSingleItemFromShoppingCart(){

        $cart_item = $this->shoppingCartQueryService()->getSingleItemFromShoppingCart($this->uriAt(0), $this->queryServiceQueryObject());
        
        $this->success($cart_item);

    }
    private function completeTheOrder(){}

    // private function changeQuantity(){

    //     $this->shoppingCartManagementService()->changeQuantityOfShoppingCartItem(
    //         $this->getAttr('product_id'),
    //         $this->getAttr('quantity',true)
    //     );
    // }

    private function emptyTheShoppingCart(){}

    private function deleteSingleItemFromShoppingCart(){

        $this->shoppingCartManagementService()->deleteSingleItemFromShoppingCart($this->uriAt(0));
  
        $this->noContent();
    }


}