<?php

namespace model\Guest\application;

use model\Guest\domain\model\IOrderRepository;
use model\common\ApplicationService;
use model\Guest\domain\model\GuestId;
use model\Guest\domain\model\IGuestRepository;
use model\Guest\domain\model\IRoomItemRepository;
use model\Guest\domain\model\Order;
use model\Guest\domain\model\OrderId;
use model\Guest\domain\model\OrderStatus;
use model\Guest\domain\model\RoomItemId;

class BasketManagementService extends ApplicationService{

    function __construct(private IOrderRepository $orders, private IGuestRepository $guests, private IRoomItemRepository $room_items){}

    public function deleteItem(string $basket_item_id){

        $basket_item = $this->existingBasketItem($basket_item_id);

        $basket_item->remove();

        $this->process($basket_item, $this->basket_items);
    }

    public function changePieceOfProduct(string $basket_item_id, int $piece){}

    private function existingBasketItem( string $ibasket_item_id) : Basket {
        $basket_item = $this->basket_items->find(new BasketId ($basket_item_id));

        if(null == $basket_item)
           throw new \NotFoundException('Item is not found');

       return $basket_item;
   }

    protected function guestId() : GuestId {
        return new GuestId($this->identity_provider->identity());
    }
}