<?php

use model\Guest\application\OrderManagementService;
use model\ServiceReservation\application\ServiceReservationManagementService;

class ControllerGuestReservation extends RestEndpoint{

    protected function get(){}

    protected function post(){

        if(!$this->uriAt(0))
            $this->addTocart();
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

    private function serviceReservationManagementService(): ServiceReservationManagementService{

        $this->load->module('ServiceReservation');

        $this->service_reservation_service = $this->module_service_reservation->service('ServiceReservationManagementService');

        return $this->service_reservation_service;
    }

    private function addToCart(){

        $this->serviceReservationManagementService()->createReservation(
            $this->getAttr('product_id'),
            $this->getAttr('reservation_time'),
            $this->getAttr('number_of_people')
        );
        
        $this->noContent();
    }

    
}