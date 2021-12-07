<?php 

namespace model\common\domain\model;

use \model\common\IComparable;

class Submodule implements IComparable {
    private SubmoduleId $id;
    private ModuleId $module_id;
    private string $name;

    function __construct(SubmoduleId $id, ModuleId $module_id, string $name) {
        $this->id = $id;
        $this->module_id = $module_id;
        $this->name = $name;
    }

    public function getId() : SubmoduleId {
        return $this->id;
    }
    
    public function moduleId() : ModuleId {
        return $this->module_id;
    }

    public function name() : string {
        return $this->name;
    }

    public function equals($submodule) : bool {
        return $this->getId()->equals($submodule->getId());
    }
}
?>