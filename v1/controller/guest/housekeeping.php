<?php

use model\Guest\application\OrderManagementService;

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

    private function orderManagementService(): OrderManagementService
    {
    $this->load->module('Guest');

    $this->order_management_service = $this->module_guest->service('OrderManagementService');

    return $this->order_management_service;
    }

    private function fetchHouseKeepingProducts(){}

    private function addToCart(){

        $this->orderManagementService()->addToCart(
            $this->getAttr('product_id'),
            $this->getAttr('quantity'),
        );
    }
}