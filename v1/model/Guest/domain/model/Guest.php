<?php

namespace model\Guest\domain\model;

use DateTime;
use model\common\Entity;
use model\Sdm\domain\model\exception\TaxiCountDownCanNotBeLaterThanOneHourException;
use model\Sdm\domain\model\OrderType;

class Guest extends Entity
{
    function __construct(
        private GuestId $id,
        private RoomId $room_id,
        private string $first_name,
        private string $last_name,
        private int $phone_no,
        private ?int $tc_kimlik,
        private ?string $passport_no
    ){}

    public function orderTaxi(OrderId $order_id, ModuleId $module_id, int $countdown, string $order_note){

        if($countdown > 60)
            $this->addException(new TaxiCountDownCanNotBeLaterThanOneHourException());


    }

    public function wakeUpAlarm(\DateTime $wake_up_time){
        
        $alarm = $wake_up_time->format('H:i');

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
            null
        );
    }
}
