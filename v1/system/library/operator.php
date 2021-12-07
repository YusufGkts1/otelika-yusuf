<?php

class Operator {
    private string $type;
    private string $id;

    function __construct(string $type, string $id) {
        $this->type = $type;
        $this->id = $id;
    }

    public function type() : string {
        return $this->type;
    }

    public function id() : string {
        return $this->id;
    }
}

?>