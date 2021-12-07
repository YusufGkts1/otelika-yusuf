<?php 

namespace model\common\application\DTO;

use \model\common\domain\model\Submodule;

class SubmoduleDTO implements \JsonSerializable{

    private string $id;
    private string $type;
    private array $attributes;

    function __construct(string $id, string $module_id) {
        $this->id = $id;
        $this->type = 'submodule';
        $this->attributes['module_id'] = $module_id;
    }

    public static function fromSubmodule(Submodule $submodule) : SubmoduleDTO {
        return new SubmoduleDTO(
            $submodule->getId()->getId(),
            $submodule->moduleId()->getId(),
            $submodule->name()
        );
    }

    public function id() : string {
        return $this->id;
    }

    public function type() : string {
        return $this->type;
    }

    public function moduleId() : string {
        return $this->attributes['module_id'];
    }

    public function jsonSerialize() {
        return get_object_vars($this);
    }
}

?>