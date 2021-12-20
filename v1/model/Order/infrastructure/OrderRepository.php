<?php

namespace model\Order\infrastructure;

use model\common\Entity;
use model\Guest\domain\model\IOrderRepository;
use model\Guest\domain\model\Order;
use model\Guest\domain\model\OrderId;

use model\common\Repository;
use model\Guest\domain\model\GuestId;
use model\Guest\domain\model\ModuleId;
use model\Guest\domain\model\ProductId;
use model\Guest\domain\model\RoomId;

class OrderRepository extends Repository implements IOrderRepository{

    function __construct(private \DB $db) {}

    public function find(OrderId $id) : ?Order {

        $dbo = $this->dboFromId($id);

        if(!$dbo)

        return null;

        return $this->orderFromDbo($dbo);

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
            `total_price` = :total_price,
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
            ':total_price' => $this->getProperty($entity,'total_price')
            ]);
        }else if($this->templateOrder($id)){
            $this->db->command("UPDATE order SET
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

    public function nextId() : OrderId {
        return new OrderId(uniqid());
	}

    private function templateOrder(OrderId $id) : bool {
		return $this->db->query("SELECT COUNT(*) as total FROM order WHERE id = :id", [
			':id' => $id->getId()
		])->row['total'] > 0;
	}

    private function orderFromDbo(array $dbo) : Order {
        return new Order(
            new OrderId ($dbo['id']),
			new GuestId ($dbo['guest_id']),
            new RoomId ($dbo['room_id']),
            new ModuleId($dbo['module_id']),
            new ProductId ($dbo['room_id']),
            $dbo['order_note'],
            new \DateTime($dbo['delivery_time']),
            $dbo['total_price']
        );
	}
}