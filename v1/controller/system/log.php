<?php 

use \model\system\log\LoggingService;
use \model\system\log\OperatorType;

use \model\IdentityAndAccess\domain\model\Personnel;

class ControllerSystemLog extends RestEndpoint {
    protected function get() {
        // (new LoggingService())->addOperation('created_personnel', 'personnel', 2, array(
        //     'id' => 2,
        //     'firstname' => 'log',
        //     'lastname' => 'test'
        // ));

        // (new LoggingService())->addOperation('updated_personnel', 'personnel', 2, array(
        //     'id' => 2,
        //     'firstname' => 'log edit',
        //     'lastname' => 'test edit'
        // ));

        // (new LoggingService())->addOperation('created_role', 'role', 2, array(
        //     'id' => 2,
        //     'name' => 'role'
        // ));

        // (new LoggingService())->addOperation('edited_role', 'role', 2, array(
        //     'id' => 2,
        //     'name' => 'role edit'
        // ));

        // (new LoggingService())->addOperation('edited_role', 'role', 2, array(
        //     'id' => 2,
        //     'name' => 'role edit edit'
        // ));

        // $data = (new LoggingService())->getLastNOperations(2);

        // $data = (new LoggingService())->getNodeLifecycle();

        $data = (new LoggingService())->getLastNOperators(1, 2);

        $this->success(array (
            'data' => $data
        ));
    }

    protected function post() {
        $this->notImplemented();
    }

    protected function patch() {
        $this->notImplemented();
    }

    protected function delete() {
        $this->notImplemented();
    }

    protected function submoduleId() : int {
        return 4;
    }

    protected function orderBySupportingFields(): array {
        return array();
    }

    protected function filterSupportingFields(): array {
        return $this->orderBySupportingFields();
    }
}

?>