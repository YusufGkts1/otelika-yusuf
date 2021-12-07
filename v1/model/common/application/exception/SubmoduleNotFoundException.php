<?php 

namespace model\common\application\exception;

use NotFoundException;

class SubmoduleNotFoundException extends \NotFoundException {
    protected $code = 802;
    protected $message = "Submodule was not found";
}
?>