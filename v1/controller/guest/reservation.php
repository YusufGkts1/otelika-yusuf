<?php

use model\InhouseReservation\application\InhouseReservationManagementService;

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

    private function serviceReservationManagementService(): InhouseReservationManagementService{

        $this->load->module('InhouseReservation');

        $this->inhouse_reservation_service = $this->module_inhouse_reservation->service('InhouseReservationManagementService');

        return $this->inhouse_reservation_service;
    }

    private function addToCart(){

        $this->serviceReservationManagementService()->createReservation(
            $this->getAttr('inhouse_service_id'),
            $this->getAttr('reservation_date_time'),
            $this->getAttr('number_of_people')
        );
        
        $this->noContent();
    }

    
}