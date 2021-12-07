<?php 

namespace model\system\log;

class Operator {

    private int $type;
    private string $id;

    function __construct(int $type, string $id) {
        $this->type = $type;
        $this->id = $id;
    }

    public function type() : int {
        return $this->type;
    }

    public function id() : string {
        return $this->id;
    }
}

?>