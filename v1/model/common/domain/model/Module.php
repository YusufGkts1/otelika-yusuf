<?php 

namespace model\common\domain\model;

use \model\common\IComparable;

class Module implements IComparable {
    private ModuleId $id;
    private string $name;

    function __construct(ModuleId $id, string $name) {
        $this->id = $id;
        $this->name = $name;
    }

    public function id() : ModuleId {
        return $this->id;
    }

    public function name() : string {
        return $this->name;
    }

    public function equals($id) : bool {
        return $this->id->equals($id);
    }
}

?>