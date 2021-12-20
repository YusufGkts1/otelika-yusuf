<?php

namespace model\Order\domain\model;

use model\Guest\domain\model\OrderId;

use model\common\Entity;
use model\common\IPersistenceProvider;

interface IOrderRepository extends IPersistenceProvider
{
    public function find(OrderId $id) : ?Order;

    public function save(Entity $entity);

    public function remove(string $id);

    public function nextId():OrderId;


}
