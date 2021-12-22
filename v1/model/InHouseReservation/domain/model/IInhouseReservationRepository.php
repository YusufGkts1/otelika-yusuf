<?php

namespace model\InhouseReservation\domain\model;

use model\common\Entity;
use model\common\IPersistenceProvider;
use model\InhouserReservation\domain\model\InhouseServiceId;

interface IInhouseServiceRepository extends IPersistenceProvider
{
    public function find(InhouseServiceId $id) : InhouseService;
    
    public function save(Entity $entity);

    public function remove(InhouseServiceId $id);

    public function nextId():InhouseServiceId;


}
