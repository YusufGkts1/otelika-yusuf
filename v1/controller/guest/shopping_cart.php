<?php

use model\Guest\application\OrderManagementService;
use model\Guest\application\OrderQueryService;

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
            $this->changeQuantityOfShoppingCartItem();
    }

    protected function delete(){
        
        if(!$this->uriAt(0))
            $this->emptyThecart();
        
        $this->deleteSingleItemFromcart();
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

    private function orderQueryService(): OrderQueryService{
        if ($this->order_query_service)
            return $this->order_query_service;

        $this->load->module('Guest');

        $this->order_query_service = $this->module_guest->service('OrderQueryService');

        return $this->order_query_service;
    }

    private function orderManagementService(): OrderManagementService{
    $this->load->module('Guest');

    $this->order_management_service = $this->module_guest->service('OrderManagementService');

    return $this->order_management_service;
    }

    private function getSelfOwnedShoppingCart(){

        $cart = $this->orderQueryService()->getSelfOwnedShoppingCart($this->queryServiceQueryObject());
        
        $this->success($cart);
    }

    private function getSingleItemFromShoppingCart(){

        $cart_item = $this->orderQueryService()->getSingleItemFromShoppingCart($this->uriAt(0), $this->queryServiceQueryObject());
        
        $this->success($cart_item);

    }
    private function completeTheOrder(){}

    private function changeQuantityOfShoppingCartItem(){}

    private function emptyThecart(){}

    private function deleteSingleItemFromcart(){

        $this->orderManagementService()->deleteSingleItemFromcart($this->uriAt(0));
  
        $this->noContent();
    }


}