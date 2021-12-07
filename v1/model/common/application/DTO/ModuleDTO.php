<?php 

namespace model\common\application\DTO;

use \model\common\domain\model\Module;

class ModuleDTO implements \JsonSerializable{

    private string $id;
    private string $type;

    function __construct(string $id) {
        $this->id = $id;
        $this->type = 'module';
    }

    public static function fromModule(Module $module) : ModuleDTO {
        return new ModuleDTO(
            $module->id()->getId()
        );
    }

    public function id() : string {
        return $this->id;
    }

    public function type() : string {
        return $this->type;
    }

    public function jsonSerialize() {
        return get_object_vars($this);
    }
}

?>