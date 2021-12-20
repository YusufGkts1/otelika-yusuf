<?php

namespace model\Taxi\domain\model;

use model\common\domain\model\GuestId;
use model\common\domain\model\RoomId;
use model\common\Entity;

class Taxi extends Entity
{
    function __construct(
        private TaxiId $id,
        private GuestId $guest_id,
        private RoomId $room_id,
        private ?int $countdown,
        private ?string $guest_note  
    ){}

}
