<?php

use model\Guest\application\OrderManagementService;

class ControllerGuestTaxi extends RestEndpoint{

    protected function get(){}

    protected function post(){

        if(!$this->uriAt(0) || $this->uriAt(0))
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

    private function orderManagementService(): OrderManagementService{

        $this->load->module('Guest');

        $this->order_management_service = $this->module_guest->service('OrderManagementService');

        return $this->order_management_service;
    }

    private function addToCart(){

        $this->orderManagementService()->addToShoppingCart(
            $this->getAttr('product_id'),
            $this->getAttr('quantity'),
        );
        
        $this->noContent();
    }

    
}