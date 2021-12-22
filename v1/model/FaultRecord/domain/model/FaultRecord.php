<?php

namespace model\FaultRecord\domain\model;

use model\common\domain\model\GuestId;
use model\common\domain\model\ProductId;
use model\common\domain\model\RoomId;
use model\common\domain\model\ServiceModuleId;
use model\common\Entity;
use model\Order\domain\model\CategoryId;

class FaultRecord extends Entity
{
    function __construct(
        private FaultRecordId $id,
        private GuestId $guest_id,
        private RoomId $room_id,
        private ProductId $product_id,
        private ServiceModuleId $module_id,
        private ?CategoryId $category_id,
        private ?string $fault_note
    ){}

}
