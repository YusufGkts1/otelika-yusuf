<?php

class ControllerGuest extends RestEndpoint{

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
               
    }

    protected function post(){
        if('basket' == $this->uriAt(0))
            $this->confirmBasket();

        if('basket' != $this->uriAt(0))
            if($this->uriAt(1)){
                $this->addToBasket();
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

    private function getHomePageCategories(){}

    private function getGuestProfile(){}

    private function fetchGuestSelfOrders(){}

    private function getGuestSelfOrder(){}

    private function getOrderBasket(){}

    private function getSingleItemFromOrderBasket(){}

    private function confirmBasket(){}

    private function addToBasket(){}

    private function cancelOrder(){}

    private function patchItemFromBasket(){}

    private function deleteItemFromBasket(){}


}