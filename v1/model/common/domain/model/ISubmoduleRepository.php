<?php 

namespace model\common\domain\model;

interface ISubmoduleRepository {
    public function findById(SubmoduleId $id) : ?Submodule;

    public function exists(SubmoduleId $id) : bool;
}
?>