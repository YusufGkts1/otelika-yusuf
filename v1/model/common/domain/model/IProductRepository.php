<?php

namespace model\common\domain\model;

use model\common\Entity;
use model\common\IPersistenceProvider;

interface IProductRepository extends IPersistenceProvider
{
    public function find(ProductId $id) : ?Product;

    public function save(Entity $entity);

    public function remove(string $id);

    public function nextId():ProductId;
}
