<?php


class ControllerGuestTaxi extends RestEndpoint{

    protected function get(){
        if(!$this->uriAt(0))
            $this->fetchHouseKeepingProducts();
        
        if($this->uriAt(0))
            $this->getSingleHouseKeepingProduct($this->uriAt(0));
    }

    protected function post(){

        if(!$this->uriAt(0) || $this->uriAt(0))
            $this->addToBasket();
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
}