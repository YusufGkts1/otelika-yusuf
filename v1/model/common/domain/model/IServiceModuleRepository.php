<?php

namespace model\common\domain\model;

use model\common\Entity;
use model\common\IPersistenceProvider;

interface IServiceModuleRepository extends IPersistenceProvider
{
    public function find(ModuleId $id) : ?Module;

    public function save(Entity $entity);

    public function remove(string $id);

    public function nextId():ModuleId;

    public function getModuleIdByProductId(ProductId $id) : ?ModuleId;

    public function getModuleIdByModuleName(string $category_name) : ?ModuleId;
}