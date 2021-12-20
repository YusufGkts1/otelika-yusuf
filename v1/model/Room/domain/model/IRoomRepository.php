<?php

namespace model\Room\domain\model;

use model\common\Entity;
use model\common\IPersistenceProvider;

interface IRoomRepository extends IPersistenceProvider
{
    public function find(RoomId $id) : ?Room;

    public function save(Entity $entity);

    public function remove(string $id);

    public function nextId():RoomId;


}
