<?php

namespace model\FaultRecord\domain\model;

use model\common\domain\model\GuestId;
use model\common\domain\model\ProductId;
use model\common\domain\model\RoomId;
use model\common\Entity;

class FaultRecord extends Entity
{
    function __construct(
        private FaultRecordId $id,
        private GuestId $guest_id,
        private RoomId $room_id,
        private ProductId $product_id,
        private ?string $fault_note
    ){}

}
