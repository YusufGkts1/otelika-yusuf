<?php

use model\Guest\application\OrderManagementService;

class ControllerUser extends RestEndpoint{

    protected function get(){

        if(!$this->uriAt(0))
            $this->getHomePageCategories();
        
        if('profile' == $this->uriAt(0)){
            if('order' == $this->uriAt(1)){
                if($this->uriAt(2)){
                    $this->getGuestSelfOrder();
                }
                $this->fetchGuestSelfOrders();
            }
            $this->getGuestProfile();
        }
         
        if('basket' == $this->uriAt(0)){
            if($this->uriAt(1)){
                $this->getSingleItemFromOrderBasket();
            }
            $this->getOrderBasket(); 
        }

        if('fault_record' == $this->uriAt(0))
            if($this->uriAt(1)){
                $this->getFaultItem();
            }
            $this->fetchFaultItems();
    }

    protected function post(){
        if('basket' == $this->uriAt(0))
            $this->confirmBasket();

        if('basket' != $this->uriAt(0))
            if($this->uriAt(1)){
                $this->addToBasket();
            }

        if('taxi' == $this->uriAt(0))
            $this->callTaxi();

        if('wake_up_service' == $this->uriAt(0))
            $this->createWakeUpAlarm();

        if('fault_record' == $this->uriAt(0)){
            if($this->uriAt(1))
                $this->sendFaultRecord();
        }
    }

    protected function patch(){
        if('profile' == $this->uriAt(0)){
            if('order' == $this->uriAt(1)){
                if($this->uriAt(2)){
                    $this->cancelOrder();
                }
            }
        }

        if('basket' == $this->uriAt(0)){
            if($this->uriAt(1)){
                $this->patchItemFromBasket();
            }
        }
    }

    protected function delete(){
        if('basket' == $this->uriAt(0)){
            if($this->uriAt(1)){
                $this->deleteItemFromBasket();
            }
        }
       
    }

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


    private function getHomePageCategories(){}

    private function getGuestProfile(){}

    private function fetchGuestSelfOrders(){}

    private function getGuestSelfOrder(){}

    private function getOrderBasket(){}

    private function getSingleItemFromOrderBasket(){}

    private function fetchFaultItems(){}

    private function getFaultItem(){}

    private function confirmBasket(){}

    private function addToBasket(){}

    private function cancelOrder(){}

    private function patchItemFromBasket(){}

    private function deleteItemFromBasket(){}

    private function sendFaultRecord(){
        $this->orderManagementService()->createFaultRecord(
            $this->uriAt(1)
        );
    }

    private function callTaxi(){

        $id = $this->orderManagementService()->callTaxi(
            $this->getAttr('countdown'),
            $this->getAttr('order_note')
            );
        
        $this->noContent();
    }

    private function createWakeUpAlarm(){

        $this->orderManagementService()->wakeUpService(
            $this->getAttr('wake_up_time')
        );
    }


}