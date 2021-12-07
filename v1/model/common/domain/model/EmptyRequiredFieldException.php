<?php 

namespace model\common\domain\model;

class EmptyRequiredFieldException extends \Exception {
    protected $message = 'Required field cannot be empty';
    protected $code = 804;
}

?>