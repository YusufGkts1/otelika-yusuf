<?php 

namespace model\common\domain\model;

use \model\common\IComparable;

class FileId implements IComparable {

    private string $id;

    function __construct(string $id) { 
        $this->id = $id;
    }

    public function getId() : string {
        return $this->id;
    }

    public function equals($id) : bool {
        return $this->id == $id->getId();
    }
}
?>