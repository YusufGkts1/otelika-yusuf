<?php 

namespace model\common\domain\model;

class InvalidFieldTypeException extends \Exception {
    protected $message = 'Field type is not recognized';
    protected $code = 806;
}

?>