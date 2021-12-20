<?php

namespace model\Alarm\domain\model;

use model\common\Entity;
use model\common\IPersistenceProvider;

interface IAlarmRepository extends IPersistenceProvider
{
    public function find(AlarmId $id) : ?Alarm;

    public function save(Entity $entity);

    public function remove(string $id);

    public function nextId():AlarmId;

}
