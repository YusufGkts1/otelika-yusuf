<?php

namespace model\Guest\domain\model;

use DateTime;
use model\common\Entity;
use model\Sdm\domain\model\exception\TaxiCountDownCanNotBeLaterThanOneHourException;
use model\Sdm\domain\model\OrderType;

class Guest extends Entity
{
    function __construct(){}

    public function orderTaxi(float $countdown, string $order_note){

        if($countdown > 60)
            $this->addException(new TaxiCountDownCanNotBeLaterThanOneHourException());

        return new Order(
            OrderType::Taxi()
        ); //Room_id buradan dönecek.
    }

    public function wakeUpAlarm(\DateTime $wake_up_time){
        return new Order(
            OrderType::WakeUpService()
        );

    }
}
