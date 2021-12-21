<?php

namespace model\common\domain\model;

use DateTime;
use model\Alarm\domain\model\Alarm;
use model\Alarm\domain\model\AlarmId;
use model\common\Entity;
use model\FaultRecord\domain\model\FaultRecord;
use model\FaultRecord\domain\model\FaultRecordId;
use model\Order\domain\model\CategoryId;
use model\Order\domain\model\ShoppingCartId;
use model\Order\domain\model\ShoppingCartItem;
use model\Order\domain\model\ShoppingCartItemId;
use model\Sdm\domain\model\exception\CallingTaxiCountDownCanNotBeLaterThanOneHourException;
use model\Taxi\domain\model\Taxi;
use model\Taxi\domain\model\TaxiId;

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

    public function id(){

        return $this->id;
    }

    public function roomId(){
        return $this->room_id;
    }

    public function orderTaxi(TaxiId $id, int $countdown, string $guest_note){

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

    public function wakeUpAlarm(AlarmId $alarm_id, DateTime $wake_up_time){
        
        $alarm = $wake_up_time->format('H:i');

        $now = date('H:i');

        if($alarm <= $now){

            $new_alarm = $wake_up_time->modify('+1 day');
            
            return new Alarm(
                $alarm_id,
                $this->id,
                $this->room_id,
                $this->phone_no,
                $new_alarm
            );
        }
        
        if($alarm > $now){
            return new Alarm(
                $alarm_id,
                $this->id,
                $this->room_id,
                $this->phone_no,
                $wake_up_time
            );    
        }

    }

    public function sendFaultRecord(FaultRecordId $fault_record_id, ProductId $broken_item_id, string $fault_note){

        return new FaultRecord(
           $fault_record_id,
           $this->id,
           $this->room_id,
           $broken_item_id,
           $fault_note

        );
    }

    // public function addToShoppingCart(ShoppingCartItemId $shopping_cart_item_id, ServiceModuleId $module_id, ?CategoryId $category_id, ProductId $product_id, ?string $order_note, ?DateTime $delivery_time, float $quantity, float $total_price){
        
    //     return new ShoppingCartItem(
    //         $shopping_cart_item_id,
    //         $this->id,
    //         $this->room_id,
    //         $module_id,
    //         $category_id,
    //         $product_id,
    //         $order_note,
    //         $delivery_time,
    //         $quantity,
    //         $total_price
    //     );
    // }

    public function addToShoppingCart(ShoppingCartId $shopping_cart_id, ShoppingCartItemId $shopping_cart_item_id, Product $product, float $quantity){

        return new ShoppingCartItem(
            $shopping_cart_id,
            $shopping_cart_item_id,
            $this->id,
            $this->room_id,
            $product->moduleId(),
            $product->categoryId(),
            $product->productId(),
            $quantity,
            $product->calculatePrice($quantity)
        );
    }
}
