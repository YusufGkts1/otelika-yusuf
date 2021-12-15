<?php

class ControllerDashboardRoom extends RestEndpoint{

    protected function get(){

        if(!$this->uriAt(0))
            $this->fetchAllRooms();
        
        $this->getSingleRoom();
    }

    protected function post(){
    
        $this->createRoom();
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

    private function fetchAllRooms(){}

    private function getSingleRoom(){}

    private function createRoom(){}
}