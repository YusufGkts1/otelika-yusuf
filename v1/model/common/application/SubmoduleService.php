<?php 

namespace model\common\application;

use \model\common\domain\model\IModuleRepository;
use \model\common\domain\model\ISubmoduleRepository;
use \model\common\domain\model\SubmoduleId;
use \model\common\domain\model\Submodule;
use \model\common\domain\model\ModuleId;
use \model\common\domain\model\Module;

use \model\common\application\DTO\SubmoduleDTO;
use \model\common\application\DTO\ModuleDTO;
use model\common\application\exception\SubmoduleNotFoundException;
use model\common\application\exception\ModuleNotFoundException;

use \model\common\infrastructure\ModuleRepository;
use \model\common\infrastructure\SubmoduleRepository;


class SubmoduleService {
    private $submodule_repository;

    function __construct(ISubmoduleRepository $submodule_repository, IModuleRepository $module_repository) {
        $this->submodule_repository = null != $submodule_repository ? $submodule_repository : new SubmoduleRepository();
        $this->module_repository = null != $module_repository ? $module_repository : new ModuleRepository();
    }

    public function getById(int $submodule_id) : ?SubmoduleDTO {
        $submodule = $this->submodule_repository->findById(new SubmoduleId($submodule_id));

        if(null == $submodule)
            return null;

        return SubmoduleDTO::fromSubmodule($submodule);
    }

    public function getParentModule(int $submodule_id) : ModuleDTO {
        $submodule = $this->existingSubmodule($submodule_id);

        $module = $this->existingModule($submodule->moduleId()->getId());

        return ModuleDTO::fromModule($module);
    }

    public function exists(int $submodule_id) : bool {
        return $this->submodule_repository->exists(new SubmoduleId($submodule_id));
    }

    private function existingSubmodule(int $id) : Submodule {
        $submodule = $this->submodule_repository->findById(new SubmoduleId($id));

        if(null == $submodule)
            throw new SubmoduleNotFoundException();

        return $submodule;
    }

    private function existingModule(int $id) : Module {
        $module = $this->module_repository->findById(new ModuleId($id));

        if(null == $module)
            throw new ModuleNotFoundException();

        return $module;
    }
}

?>