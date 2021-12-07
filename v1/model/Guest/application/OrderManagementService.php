<?php

namespace model\Guest\application;

use model\Guest\domain\model\IOrderRepository;
use model\common\ApplicationService;
use model\Guest\domain\model\GuestId;
use model\Guest\domain\model\IGuestRepository;

class OrderManagementService extends ApplicationService{

    function __construct(private IOrderRepository $orders, private IGuestRepository $guests){}

    public function callTaxi(
        float $countdown,
        string $order_note
    ){
        $id = $this->orders->nextId();

        $guest = $this->guests->find($this->guestId());

        $order = $guest->orderTaxi($countdown, $order_note);

        $this->process($order, $this->orders);

    	return $id->getId();
    }

    public function wakeUpService(
        \DateTime $wake_up_time
    ){
        $id = $this->orders->nextId();

        $guest = $this->guests->find($this->guestId());

        $order = $guest->wakeUpAlarm($wake_up_time);

        $this->process($order, $this->orders);
 
    	return $id->getId();

    }

    protected function guestId() : GuestId {
        return new GuestId($this->identity_provider->identity());
    }
}