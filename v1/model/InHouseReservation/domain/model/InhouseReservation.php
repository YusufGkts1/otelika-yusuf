<?php

namespace model\InhouseReservation\domain\model;

use DateTime;
use model\common\domain\model\GuestId;
use model\common\domain\model\RoomId;
use model\common\Entity;
use model\InhouserReservation\domain\model\InhouseReservationId;
use model\InhouserReservation\domain\model\InhouseServiceId;

class InhouseReservation extends Entity
{
    function __construct(
        private InhouseReservationId $id,
        private InhouseServiceId $inhouse_service_id,
        private GuestId $guest_id,
        private RoomId $room_id,
        private DateTime $reservation_date,
        private int $numer_of_people
    ){}

}
