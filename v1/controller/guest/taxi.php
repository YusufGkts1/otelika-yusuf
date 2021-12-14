<?php

use model\Guest\application\TaxiManagementService;

class ControllerGuestTaxi extends RestEndpoint{

    protected function get(){}

    protected function post(){

        if(!$this->uriAt(0))
            $this->callTaxi();    
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

    private function taxiManagementService(): TaxiManagementService
    {
    $this->load->module('Guest');

    $this->taxi_management_service = $this->module_guest->service('TaxiManagementService');

    return $this->taxi_management_service;
    }

    private function callTaxi(){

        $this->taxiManagementService()->callTaxi(
            $this->getAttr('countdown'),
            $this->getAttr('guest_note')
        );
        
        $this->noContent();
    }

}