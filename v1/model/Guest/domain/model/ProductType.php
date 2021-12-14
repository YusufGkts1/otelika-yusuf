<?php 

namespace model\Guest\domain\model;

use MyCLabs\Enum\Enum;

class ProductType extends enum {
    const RoomService = 1;
    const Minibar = 2;
    const Housekeeping = 3;
    const Reservation = 4;
    const FaultRecord = 5;

}

?>