<?php

use model\Guest\application\OrderManagementService;
use model\Guest\application\ShoppingCartManagementService;

class ControllerGuestMinibar extends RestEndpoint{

    protected function get(){

        if(!$this->uriAt(0))
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

    private function shoppingCartManagementService(): ShoppingCartManagementService
    {
    $this->load->module('Guest');

    $this->shopping_cart_management_service = $this->module_guest->service('ShoppingCartManagementService');

    return $this->shopping_cart_management_service;
    }

    private function fetchMinibarProducts(){}

    private function addToCart(){

        $this->shoppingCartManagementService()->addToShoppingCart(
            $this->getAttr('module_id'),
            $this->getAttr('product_id'),
            $this->getAttr('quantity'),
            $this->getAttr('price')
        );
    }
}