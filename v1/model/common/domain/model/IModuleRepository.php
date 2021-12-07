<?php 

namespace model\common\domain\model;

interface IModuleRepository {
    public function findById(ModuleId $id) : ?Module;
}
?>