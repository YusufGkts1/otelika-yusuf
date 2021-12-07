<?php 

namespace DB\exception;

class UniqueConstraintException extends \Exception {
   
    function __construct($message) {
        $this->message = $message;
    }

    public function getKey() {
        preg_match("/.*for key \'(?<key>.*)\'.*/", $this->getMessage(), $matches);

        return $matches['key'];
    }
}
?>