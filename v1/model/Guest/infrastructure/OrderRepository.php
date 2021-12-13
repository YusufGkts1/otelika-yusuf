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

        if(!$this->templateOrder($id)){
            $this->db->command("INSERT INTO `order`
            SET
            `id` = :id,
            `guest_id` = :guest_id,
            `room_id` = :room_id,
            `module_id` = :module_id,
            `category_id` = :category_id,
            `product_id` = :product_id,
            `order_note` = :order_note,
            `delivery_time` = :delivery_time,
            `total_amount` = :total_amount,
            `created_on` = NOW()",
            [
            ':id' => $id->getId(),
            ':guest_id' => $this->getProperty($entity, 'guest_id')->format,
            ':room_id' => $this->getProperty($entity, 'room_id'),
            ':module_id' =>  $this->getProperty($entity, 'module_id'),
            ':category_id' =>  $this->getProperty($entity, 'categeory_id'),
            ':product_id' =>  $this->getProperty($entity, 'product_id'),
            ':order_note' => $this->getProperty($entity, 'order_note'),
            ':delivery_time' => $this->getProperty($entity, 'delivery_time'),
            ':total_amount' => $this->getProperty($entity,'total_amount')
            ]);
        }else if($this->templateOrder($id)){
            $this->db->command("UPDATE registration SET
            `status` = :status
             WHERE id = :id",
            [
           'id' => $id->getId(),
            ':status' => $this->getProperty($entity,'status')
            ]);
        }
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

    private function templateOrder(OrderId $id) : bool {
		return $this->db->query("SELECT COUNT(*) as total FROM order WHERE id = :id", [
			':id' => $id->getId()
		])->row['total'] > 0;
	}
}