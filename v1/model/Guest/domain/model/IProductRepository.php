<?php

namespace model\Guest\domain\model;

use model\common\Entity;
use model\common\IPersistenceProvider;

interface IProductRepostitory extends IPersistenceProvider
{
    public function find(ProductId $id) : ?Product;

    public function save(Entity $entity);

    public function remove(string $id);

    public function nextId():ProductId;
}
