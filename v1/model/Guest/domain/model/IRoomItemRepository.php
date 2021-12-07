<?php

namespace model\Guest\domain\model;

use model\common\Entity;
use model\common\IPersistenceProvider;

interface IRoomItemRepository extends IPersistenceProvider
{
    public function find(RoomItemId $id) : ?RoomItem;

    public function save(Entity $entity);

    public function remove(string $id);

    public function nextId():RoomItemId;


}
