<?php 

namespace model\common\domain\model;

use \model\common\IComparable;

class SubmoduleId implements IComparable {

    private int $id;

    function __construct(int $id) { 
        $this->id = $id;
    }

    public function getId() : int {
        return $this->id;
    }

    public function equals($id) : bool{
        return $this->id == $id->getId();
    }
}
?>