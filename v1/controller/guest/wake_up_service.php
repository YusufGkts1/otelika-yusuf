<?php

use model\Guest\application\AlarmManagementService;

class ControllerGuestWakeUpService extends RestEndpoint{

    protected function get(){}

    protected function post(){

        if(!$this->uriAt(0))
            $this->createWakeUpAlarm();    
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

    private function alarmManagementService(): AlarmManagementService{

        $this->load->module('Guest');

        $this->alarm_management_service = $this->module_guest->service('AlarmManagementService');

        return $this->alarm_management_service;
    }

    private function createWakeUpAlarm(){

        $this->alarmManagementService()->wakeUpService(
            $this->getAttr('wake_up_time')
        );

        $this->noContent();
    }

}