<?php

namespace model\FaultRecord\infrastructure;

use model\common\Entity;
use model\common\Repository;
use model\Guest\domain\model\FaultRecord;
use model\Guest\domain\model\FaultRecordId;
use model\Guest\domain\model\GuestId;
use model\Guest\domain\model\IFaultRecordRepository;
use model\Guest\domain\model\ProductId;
use model\Guest\domain\model\RoomId;

class FaultRecordRepository extends Repository implements IFaultRecordRepository{

    public function find(FaultRecordId $id) : FaultRecord{

        $dbo = $this->dboFromId($id);

        if(!$dbo)
        return null;

        return $this->faultRecordFromDbo($dbo);
    }
    
    function __construct(
        private \DB $db
    ){}

    public function save(Entity $entity){

        /** @var FaultRecord $entity */
        /** @var FaultRecordId $id */
        $id = $this->getProperty($entity, 'id');

        if(!$this->templateFaultRecord($id)){
            $this->db->command("INSERT INTO `fault_record` SET
                `id` = :id,
                `guest_id` = :guest_id,
                `room_id` = :room_id,
                `product_id` = :product_id,
                `fault_note` = :fault_note,
                'status' = :status
                `created_on` = NOW()",
                [
                ':id' => $id->getId(),
                ':guest_id' => $this->getProperty($entity, 'guest_id'),
                ':room_id' => $this->getProperty($entity, 'room_id'),
                ':product_id' => $this->getProperty($entity, 'product_id'),
                ':fault_note' => $this->getProperty($entity, 'fault_note'),
                ':status' => 1
            ]);
        } else if($this->templateFaultRecord($id)){

            $this->db->command("UPDATE fault_record SET
                `status` = :status
                WHERE id = :id",
                [
                'id' => $id->getId(),
                ':status' => 2
            ]);
        }
    }

    public function remove(string $id){}

    public function nextId() : FaultRecordId {

        return new FaultRecordId(uniqid());
    }

    private function templateFaultRecord(FaultRecordId $id) : bool {

		return $this->db->query("SELECT COUNT(*) as total FROM fault_record WHERE id = :id", [
			':id' => $id->getId()
		])->row['total'] > 0;
	}

    private function dboFromId(FaultRecordId $id) {

        return $this->db->query("SELECT * FROM taxi_order WHERE id = :id", [
			':id' => $id->getId()
		])->row;
	}
    
    private function faultRecordFromDbo(array $dbo) : FaultRecord {
        
        return new FaultRecord(
			new FaultRecordId ($dbo['id']),
			new GuestId ($dbo['guest_id']),
            new RoomId ($dbo['room_id']),
            new ProductId ($dbo['room_id']),
            $dbo['fault_note']
		);

	}

}



