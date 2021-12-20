<?php

namespace model\Category\domain\model;

use model\common\Entity;
use model\common\IPersistenceProvider;

interface ICategoryRepository extends IPersistenceProvider
{
    public function find(CategoryId $id) : ?Category;

    public function save(Entity $entity);

    public function remove(string $id);

    public function nextId(): CategoryId;


}
