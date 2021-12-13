<?php

namespace model\Guest\application;

use model\Guest\domain\model\IOrderRepository;
use model\common\ApplicationService;
use model\Guest\domain\model\GuestId;
use model\Guest\domain\model\IBasketRepository;
use model\Guest\domain\model\IGuestRepository;
use model\Guest\domain\model\IRoomItemRepository;
use model\Guest\domain\model\Order;
use model\Guest\domain\model\OrderId;
use model\Guest\domain\model\OrderStatus;
use model\Guest\domain\model\ProductId;
use model\Guest\domain\model\RoomItemId;

class BasketManagementService extends ApplicationService{

    function __construct(private IOrderRepository $orders, private IGuestRepository $guests, private IRoomItemRepository $room_items, private IBasketRepository $baskets){}

    // public function deleteItem(ProductId $basket_item_id){

    //     $basket_item = $this->existingBasketItem($basket_item_id);

    //     $basket_item->remove();

    //     $this->process($basket_item, $this->basket_items);
    // }

    public function changePieceOfProduct(ProductId $basket_item_id, float $piece){

        $basket = $this->baskets->findBasketByGuestId($this->guestId());

        if(!$basket)
            throw new \NotFoundException('Basket is not found.');

        $basket->changePieceOfBasketItem($basket_item_id, $piece);
        
    }

//     private function existingProductInBasket(ProductId $basket_item_id) : Product {
//         $basket_item = $this->baskets->findProductInBasket(new ProductId ($basket_item_id));

//         if(null == $basket_item)
//            throw new \NotFoundException('Product is not found');

//        return $basket_item;
//    }

    protected function guestId() : GuestId {
        return new GuestId($this->identity_provider->identity());
    }
}