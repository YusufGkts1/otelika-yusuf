<?php

namespace model\Guest\domain\model;

use model\Guest\domain\model\OrderId;

use model\common\Entity;
use model\common\IPersistenceProvider;

interface IFaultRecordRepository extends IPersistenceProvider
{
    public function find(FaultRecordId $id) : ?FaultRecord;

    public function save(Entity $entity);

    public function remove(string $id);

    public function nextId():FaultRecordId;

}
