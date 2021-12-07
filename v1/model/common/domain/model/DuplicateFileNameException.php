<?php 

namespace model\common\domain\model;

class DuplicateFileNameException extends \Exception {
    protected $message = 'File names must be unique';
    protected $code = 805;
}

?>