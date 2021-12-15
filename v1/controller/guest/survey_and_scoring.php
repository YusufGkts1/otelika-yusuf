<?php

use model\Survey\application\SurveyManagementService;

class ControllerGuestSurveyAndScoring extends RestEndpoint{

    protected function get(){}

    protected function post(){
        
        $this->sendSurvey();
        $this->sendScoring();    
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

    private function surveyManagementService(): SurveyManagementService
    {
    $this->load->module('Guest');

    $this->survey_management_service = $this->module_guest->service('SurveyManagementService');

    return $this->survey_management_service;
    }

    private function sendSurvey(){

        $this->surveyManagementService()->completeSurvey(
            $this->getAttr('countdown'),
            $this->getAttr('guest_note')
        );
        
        $this->noContent();
    }

    private function sendScoring(){

        $this->scoringManagementService()->completeScoring(
            $this->getAttr('countdown'),
            $this->getAttr('guest_note')
        );
        
        $this->noContent();
    }

}