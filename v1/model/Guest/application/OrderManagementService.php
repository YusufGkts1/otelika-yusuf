<?php

namespace model\Guest\application;

use model\Guest\domain\model\IOrderRepository;
use model\common\ApplicationService;
use model\Guest\domain\model\GuestId;
use model\Guest\domain\model\IGuestRepository;
use model\Guest\domain\model\IRoomItemRepository;
use model\Guest\domain\model\RoomItemId;

class OrderManagementService extends ApplicationService{

    function __construct(private IOrderRepository $orders, private IGuestRepository $guests, private IRoomItemRepository $room_items){}

    public function callTaxi(float $countdown, string $order_note)
    {
        $id = $this->orders->nextId();

        $guest = $this->guests->find($this->guestId());

        $order = $guest->orderTaxi($countdown, $order_note);

        $this->process($order, $this->orders);

    	return $id->getId();
    }

    public function wakeUpService(\DateTime $wake_up_time)
    {
        $id = $this->orders->nextId();

        $guest = $this->guests->find($this->guestId());

        $order = $guest->wakeUpAlarm($wake_up_time);

        $this->process($order, $this->orders);
 
    	return $id->getId();

    }

    public function createFaultRecord(RoomItemId $broken_item_id)
    {
        $id = $this->orders->nextId();

        $guest = $this->guests->find($this->guestId());

        $broken_item = $this->room_items->find($broken_item_id); //Hasar kaydı yapılacak olan ürününü id'si dönecek.

        $order = $guest->sendFaultRecord($broken_item);

        $this->process($order, $this->orders);
 
    	return $id->getId();

    }

    protected function guestId() : GuestId {
        return new GuestId($this->identity_provider->identity());
    }
}