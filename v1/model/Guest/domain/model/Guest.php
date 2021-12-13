<?php

namespace model\Guest\domain\model;

use DateTime;
use model\common\Entity;
use model\Sdm\domain\model\exception\CallingTaxiCountDownCanNotBeLaterThanOneHourException;

class Guest extends Entity
{
    function __construct(
        private GuestId $id,
        private RoomId $room_id,
        private string $first_name,
        private string $last_name,
        private int $phone_no,
        private ?int $citizenship_no,
        private ?string $passport_no
    ){}

    public function orderTaxi(OrderId $id, int $countdown, string $guest_note){

        if($countdown > 60)
            $this->addException(new CallingTaxiCountDownCanNotBeLaterThanOneHourException());        
    
        return new Taxi(
            $id,
            $this->id,
            $this->room_id,
            $countdown,
            $guest_note
        );
    }

    public function wakeUpAlarm(DateTime $wake_up_time){
        
        $alarm = $wake_up_time->format('H:i');

        $now = date('H:i');

        if($alarm <= $now){

            $new_alarm = strtotime('+1 day', $alarm);
            
            return new Alarm();
        }
        
        if($alarm > $now){
            return new Alarm();    
        }

    }

    public function sendFaultRecord(OrderId $order_id, ModuleId $module_id, ProductId $broken_item_id, string $fault_note){

        return new Order(
            $order_id,
            $this->id,
            $this->room_id,
            $module_id,
            $broken_item_id,
            $fault_note,
            null,
            0
        );
    }
}
