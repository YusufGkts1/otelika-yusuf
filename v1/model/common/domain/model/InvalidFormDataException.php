<?php 

namespace model\common\domain\model;

class InvalidFormDataException extends \Exception {
    protected $message = 'Invalid form data';
    protected $code = 803;
}

?>