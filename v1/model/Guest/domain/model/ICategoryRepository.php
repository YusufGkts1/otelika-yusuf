<?php

namespace model\Guest\domain\model;

use model\common\Entity;
use model\common\IPersistenceProvider;

interface ICategoryRepository extends IPersistenceProvider
{
    public function find(GuestId $id) : ?Guest;

    public function save(Entity $entity);

    public function remove(string $id);

    public function nextId():GuestId;


}
