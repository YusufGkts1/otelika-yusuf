<?php

namespace model\Alarm\domain\model;

use DateTime;
use model\common\Entity;

class Alarm extends Entity
{
    function __construct(
        private AlarmId $id,
        private GuestId $guest_id,
        private RoomId $room_id,
        private int $phone_no,
        private DateTime $wake_up_time
    ){}

}
