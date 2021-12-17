<?php

use model\Guest\application\FaultRecordManagementService;
use model\Guest\application\ProductQueryService;

class ControllerGuestFaultRecord extends RestEndpoint{

    protected function get(){

        if(!$this->uriAt(0)){

            $this->fetchFaultRecordProducts();
        }
    }

    protected function post(){

        if(!$this->uriAt(0))
            $this->sendFaultRecord();
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

    private function faultRecordManagementService(): FaultRecordManagementService{

        $this->load->module('Guest');

        $this->fault_record_management_service = $this->module_guest->service('FaultRecordManagementService');

        return $this->fault_record_management_service;
    }

    private function productQueryService(): ProductQueryService{
        if ($this->product_query_service)
            return $this->product_query_service;

        $this->load->module('Guest');

        $this->product_query_service = $this->module_guest->service('ProductQueryService');

        return $this->product_query_service;
    } 

    private function fetchFaultRecordProducts(){

        $fault_record_products = $this->productQueryService()->fetchFaultRecordProducts($this->queryServiceQueryObject());

		$this->success($fault_record_products);
    }

    private function sendFaultRecord(){

        $this->faultRecordManagementService()->createFaultRecord(
            $this->getAttr('module_id'),
            $this->getAttr('product_id'),
            $this->getAttr('fault_note')
        );

        $this->noContent();

    }

   

}