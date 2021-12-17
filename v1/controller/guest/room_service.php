<?php

use model\Guest\application\ShoppingCartManagementService;

class ControllerGuestRoomService extends RestEndpoint{

    protected function get(){

        if(!$this->uriAt(0))
            $this->fetchRoomServiceProducts();
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

    private function fetchRoomServiceProducts(){}

    private function addToCart(){

        $this->shoppingCartManagementService()->addToShoppingCart(
            $this->getAttr('module_id'),
            $this->getAttr('category_id'),
            $this->getAttr('product_id'),
            $this->getAttr('quantity'),
        );
    }
}