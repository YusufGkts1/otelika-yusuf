<?php 

namespace model\Sdm\domain\model;

use MyCLabs\Enum\Enum;

class OrderType extends enum {
    const RoomService = 1;
    const Minibar = 2;
    const Housekeeping = 3;
    const Reservation = 4;
    const Taxi = 5;
    const Broken = 6;
    const WakeUpService = 7;



}

?>