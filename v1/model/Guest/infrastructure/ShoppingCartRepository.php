<?php

namespace model\Guest\infrastructure;

use model\common\Repository;
use model\common\Entity;
use model\Guest\domain\model\IShoppingCartRepository;
use model\StudentStation\domain\model\StationId;
use model\StudentStation\domain\model\StudentId;
use model\StudentStation\domain\model\RegistrationId;
use model\StudentStation\domain\model\Registration;
use model\StudentStation\domain\model\RegistrationStatus;


class ShoppingCartRepository extends Repository implements IShoppingCartRepository{
    

    function __construct(
        private \DB $db,
    ) {}

    public function find(RegistrationId $id) : ?Registration{

        $dbo = $this->dboFromId($id);

        if(!$dbo)
        return null;

        return $this->registrationFromDbo($dbo);

    }

    public function save(Entity $entity){
        /** @var ShoppingCart $entity */
   		/** @var ShoppingCartId $id */
            $id = $this->getProperty($entity, 'id');

        if(!$this->templateRegistration($id)){
            $this->db->command("INSERT INTO `registration`
            SET
            `id` = :id,
            `student_id` = :student_id,
            `station_id` = :station_id,
            `date` = :date,
            `entry_time` = :entry_time,
            `exit_time` = :exit_time,
            `seat` = :seat,
            `status` = :status,
            `qrtoken` = :qrtoken,
            `created_on` = NOW()" ,
            [
            ':id' => $id->getId(),
            ':student_id' => $student_id->getId(),
            ':station_id' => $station_id->getId(),
            ':date' =>  $this->getProperty($entity, 'date')->format('Y-m-d'),
            ':entry_time' =>  $this->getProperty($entity, 'entry_time')->format('Y-m-d H:i:s'),
            ':exit_time' =>  $this->getProperty($entity, 'exit_time')->format('Y-m-d H:i:s'),
            ':seat' => $this->getProperty($entity, 'seat'),
            ':status' => $this->getProperty($entity, 'status'),
            ':qrtoken' => $this->getProperty($entity,'qrtoken')
            ]);
        }else if($this->templateRegistration($id)){
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

    public function nextId() : RegistrationId {
        return new RegistrationId(uniqid());
	}

    private function templateRegistration(RegistrationId $id) : bool {
		return $this->db->query("SELECT COUNT(*) as total FROM registration WHERE id = :id", [
			':id' => $id->getId()
		])->row['total'] > 0;
	}

    private function dboFromId(RegistrationId $id) {


        return $this->db->query("SELECT * FROM registration WHERE id = :id", [
			':id' => $id->getId()
		])->row;
	}

    public function quotaExists($station_id, $date, $entry_time, $exit_time, $seat): bool
    {
        $d= $date->format('Y-m-d');
        $i_time = $entry_time->format('Y-m-d H:i:s');
        $o_time = $exit_time->format('Y-m-d H:i:s');
        $quota = $this->db->query("SELECT COUNT(`seat`) as total FROM registration WHERE station_id = :station_id AND `date` = :date AND entry_time >= :i_time AND exit_time <= :o_time AND `status` = :status",[
            ':station_id' => $station_id,
            ':date' => $d,
            ':i_time' => $i_time,
            ':o_time' => $o_time,
            ':status' => 1,
        ])->row['total'];
        $totalQuota = $this->db->query('SELECT seat FROM station WHERE id = :station_id',[
            ':station_id' => $station_id
        ])->row;
        if($quota >= count(json_decode($totalQuota['seat'])))
            return false;
        else
            return true;
    }

    public function registrationExists($student_id, $station_id, $date, $entry_time,  $exit_time,  $seat): bool{
        $d = $date->format('Y-m-d');
        $i_time = $entry_time->format('Y-m-d H:i:s');
        $o_time = $exit_time->format('Y-m-d H:i:s');
    
        $repetitive = $this->db->query("SELECT * FROM registration WHERE student_id = :student_id AND station_id = :station_id AND `date` = :date AND entry_time >= :i_time AND exit_time <= :o_time AND `status` = :status", [
            'student_id' => $student_id,
            ':station_id' => $station_id,
            ':date' => $d,
            ':i_time' => $i_time,
            ':o_time' => $o_time,
            ':status' => 1
        ])->row;

        if($repetitive){
            return true;
        }else 
            return false;

    }

    public function seatExists($station_id, $date, $entry_time, $exit_time, $seat): bool{
        $d = $date->format('Y-m-d');
        $i_time = $entry_time->format('Y-m-d H:i:s');
        $o_time = $exit_time->format('Y-m-d H:i:s');
        $result = $this->db->query("SELECT * FROM registration WHERE station_id = :station_id AND `date` = :date AND entry_time >= :i_time AND exit_time <= :o_time AND seat = :seat AND `status` = :status",[
            ':station_id' => $station_id,
            ':date' => $d,
            ':i_time' => $i_time,
            ':o_time' => $o_time,
            ':seat' => $seat,
            ':status' => 1,
        ])->row;
     

        if($result){
            return true;
        }else 
        return false;

    }

    private function registrationFromDbo(array $dbo) : Registration {
        return new Registration(
			new RegistrationId($dbo['id']),
			new StudentId ($dbo['student_id']),
            new StationId ($dbo['station_id']),
            new \DateTime ($dbo['date']),
            new \DateTime ($dbo['entry_time']),
            new \DateTime ($dbo['exit_time']),
			$dbo['seat'],
            new RegistrationStatus((int)$dbo['status']),
            $dbo['qrtoken'],
		);

	}

}