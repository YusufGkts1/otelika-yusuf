<?php

use model\Order\application\ShoppingCartItemManagementService;

class ControllerGuestMinibar extends RestEndpoint{

    protected function get(){

        $this->fetchMinibarProducts();
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

    private function fetchMinibarProducts(){}

    private function addToCart(){

        $this->shoppingCartItemManagementService()->addToShoppingCart(
            $this->getAttr('product_id'),
            $this->getAttr('quantity')
        );
    }
}