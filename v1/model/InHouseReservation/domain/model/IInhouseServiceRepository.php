<?php

namespace model\InhouseReservation\domain\model;

use model\common\Entity;
use model\common\IPersistenceProvider;
use model\InhouserReservation\domain\model\InhouseReservationId;

interface IInhouseReservationRepository extends IPersistenceProvider
{
    public function find(InhouseReservationId $id) : InhouseReservation;
    
    public function save(Entity $entity);

    public function remove(InhouseReservationId $id);

    public function nextId():InhouseReservationId;


}
