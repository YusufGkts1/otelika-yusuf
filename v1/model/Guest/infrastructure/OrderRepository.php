<?php

namespace model\Guest\infrastructure;

use model\Guest\domain\model\IOrderRepository;
use model\Guest\domain\model\Order;
use model\Guest\domain\model\OrderId;

use model\common\Repository;

class OrderRepository extends Repository implements IOrderRepository{

    function __construct(private \DB $db) {}

    public function find(OrderId $id) : ?Order {

        $dbo = $this->dboFromId($id);

        if(!$dbo)

        return null;

        return $this->transactionFromDbo($dbo);

    }

    public function save(Entity $entity){
        
        /** @var Order $entity */
		/** @var OrderId $id */
        $id = $this->getProperty($entity, 'id');
    }

    public function remove(string $id){}

    private function dboFromId(OrderId $id) {

        return $this->db->query("SELECT * FROM `order` WHERE id = :id", [
            ':id' => $id->getId()
        ])->row;
    }

    private function transactionFromDbo(array $dbo) : Order {
        return new Order();
	}

    public function nextId() : OrderId {
        return new OrderId(uniqid());
	}
}