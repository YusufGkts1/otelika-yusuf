<?php 

namespace model\common\application\exception;

class ModuleNotFoundException extends \NotFoundException {
    protected $code = 801;
    protected $message = "Module was not found";
}
?>