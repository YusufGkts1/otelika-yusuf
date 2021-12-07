<?php 

namespace model\common\domain\model;

class InvalidFileCountException extends \Exception {
    protected $message = 'File count and number of FormFields with type `File` must match';
    protected $code = 805;
}

?>