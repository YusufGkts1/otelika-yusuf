<?php

use model\Order\application\ShoppingCartItemManagementService;

class ControllerGuestHouseKeeping extends RestEndpoint{

    protected function get(){

        if(!$this->uriAt(0))
            $this->fetchHouseKeepingProducts();
    }

    protected function post(){

        $this->addToCart();
    }

    protected function patch(){}

    protected function delete(){}

    protected function submoduleId(): int{
        return 0;
    }

    protected function filterSupportingFields(): array{
        return array();
    }

    protected function orderBySupportingFields(): array{
        return $this->filterSupportingFields();
    }

    private function shoppingCartItemManagementService(): ShoppingCartItemManagementService
    {
    $this->load->module('Order');

    $this->shopping_cart_item_management_service = $this->module_order->service('ShoppingCartItemManagementService');

    return $this->shopping_cart_item_management_service;
    }

    private function fetchHouseKeepingProducts(){}

    private function addToCart(){

        $this->shoppingCartItemManagementService()->addToShoppingCart(
            $this->getAttr('module_id'),
            $this->getAttr('category_id', true),
            $this->getAttr('product_id'),
            $this->getAttr('order_note', true),
            $this->getAttr('delivery_time', true),
            $this->getAttr('quantity')
        );
    }
}