<?php 

namespace model\common;

class ExceptionCollection extends \Exception {
    private array $exceptions;

    function __construct($exceptions) {
        $this->exceptions = $exceptions;
    }

    public function getExceptions() {
        return $this->exceptions;
    }

    public function addException(\Exception $exception) {
        $this->exceptions[] = $exception;
    }

    public function exceptionCount() : int {
        return count($this->exceptions);
    }
}
?>