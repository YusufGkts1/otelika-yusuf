<?php

namespace model\Guest\infrastructure;

use model\common\Entity;
use model\common\Repository;
use model\Guest\domain\model\Alarm;
use model\Guest\domain\model\AlarmId;
use model\Guest\domain\model\GuestId;
use model\Guest\domain\model\IAlarmRepository;
use model\Guest\domain\model\RoomId;

class AlarmRepository extends Repository implements IAlarmRepository{

    public function find(AlarmId $id) : Alarm{

        $dbo = $this->dboFromId($id);

        if(!$dbo)
        return null;

        return $this->alarmFromDbo($dbo);
    }
    
    function __construct(
        private \DB $db
    ){}

    public function save(Entity $entity){

        /** @var Alarm $entity */
        /** @var AlarmId $id */
        $id = $this->getProperty($entity, 'id');

        if(!$this->templateAlarm($id)){
            $this->db->command("INSERT INTO `alarm` SET
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
        } else if($this->templateAlarm($id)){

            $this->db->command("UPDATE alarm SET
                `status` = :status
                WHERE id = :id",
                [
                'id' => $id->getId(),
                ':status' => 2
            ]);
        }
    }

    public function remove(string $id){}

    public function nextId() : AlarmId {

        return new AlarmId(uniqid());
    }

    private function templateAlarm(AlarmId $id) : bool {

		return $this->db->query("SELECT COUNT(*) as total FROM alarm WHERE id = :id", [
			':id' => $id->getId()
		])->row['total'] > 0;
	}

    private function dboFromId(AlarmId $id) {

        return $this->db->query("SELECT * FROM alarm WHERE id = :id", [
			':id' => $id->getId()
		])->row;
	}
    
    private function alarmFromDbo(array $dbo) : Alarm {
        
        return new Alarm(
			new AlarmId ($dbo['id']),
			new GuestId ($dbo['guest_id']),
            new RoomId ($dbo['room_id']),
			$dbo['phone_no'],
            new \DateTime($dbo['wake_up_time']) 
		);

	}

}



