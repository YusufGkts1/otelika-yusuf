<?php

namespace model\Guest\infrastructure;

use model\common\Entity;
use model\common\Repository;
use model\Guest\domain\model\FaultRecord;
use model\Guest\domain\model\FaultRecordId;

class TaxiRepository extends Repository implements ITaxiRepository{

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
            $this->db->command("INSERT INTO `taxi_order` SET
                `id` = :id,
                `guest_id` = :guest_id,
                `room_id` = :room_id,
                `countdown` = :countdown,
                `guest_note` = :guest_note,
                'status' = :status
                `created_on` = NOW()",
                [
                ':id' => $id->getId(),
                ':guest_id' => $this->getProperty($entity, 'guest_id'),
                ':room_id' => $this->getProperty($entity, 'room_id'),
                ':countdown' => $this->getProperty($entity, 'countdown'),
                ':guest_note' => $this->getProperty($entity, 'guest_note'),
                ':status' => 1
            ]);
        } else if($this->templateTaxi($id)){

            $this->db->command("UPDATE taxi_order SET
                `status` = :status
                WHERE id = :id",
                [
                'id' => $id->getId(),
                ':status' => 2
            ]);
        }
    }

    public function remove(string $id){}

    public function nextId() : TaxiId {

        return new TaxiId(uniqid());
    }

    private function templateTaxi(TaxiId $id) : bool {

		return $this->db->query("SELECT COUNT(*) as total FROM taxi_order WHERE id = :id", [
			':id' => $id->getId()
		])->row['total'] > 0;
	}

    private function dboFromId(TaxiId $id) {

        return $this->db->query("SELECT * FROM taxi_order WHERE id = :id", [
			':id' => $id->getId()
		])->row;
	}
    
    private function faultRecordFromDbo(array $dbo) : Taxi {
        
        return new Taxi(
			new TaxiId ($dbo['id']),
			new GuestId ($dbo['guest_id']),
            new RoomId ($dbo['room_id']),
			$dbo['countdown'],
            $dbo['guest_note']
		);

	}

}



