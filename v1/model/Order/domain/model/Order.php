<?php

namespace model\Order\domain\model;

use model\common\domain\model\GuestId;
use model\common\domain\model\ProductId;
use model\common\domain\model\RoomId;
use model\common\domain\model\ServiceModuleId;
use model\common\Entity;

class Order extends Entity
{
    function __construct(
        private OrderId $id,
        private GuestId $guest_id,
        private RoomId $room_id,
        private ServiceModuleId $module_id,
        private ?CategoryId $category_id,
        private ProductId $product_id,
        private ?string $order_note,
        private ?\DateTime $delivery_time,
        private ?float $total_price
    ){}


    public function cancelAllCart(){
        $this->status = OrderStatus::Canceled();
        
        return $this->status;
    }

    public function cancelSingleOrder(){}

    public function remove() {
		$this->_remove();
	}

}
