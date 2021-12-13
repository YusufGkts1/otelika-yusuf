<?php

namespace model\Guest\domain\model;

use model\common\Entity;
use model\common\IPersistenceProvider;

interface ITaxiRepository extends IPersistenceProvider
{
    public function find(TaxiId $id) : ?Taxi;

    public function save(Entity $entity);

    public function remove(string $id);

    public function nextId():TaxiId;

}
