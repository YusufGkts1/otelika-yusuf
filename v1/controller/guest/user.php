<?php

use model\Guest\application\OrderManagementService;

class ControllerUser extends RestEndpoint{

    protected function get(){}

    protected function post(){}

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