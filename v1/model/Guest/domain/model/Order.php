<?php

namespace model\Guest\domain\model;

use model\common\Entity;

class Order extends Entity
{
    function __construct(
        private OrderId $id,
        private GuestId $guest_id,
        private RoomId $room_id,
        private ModuleId $module_id,
        //private ?CategoryId $category_id,
        private ProductId $product_id,
        private ?string $order_note,
        private ?\DateTime $delivery_time,
        private float $total_amount
    ){}


    public function cancel(){

        $canceled = OrderStatus::Cancelled();
        $this->status = $canceled;

    return $this->status;

    }
}
