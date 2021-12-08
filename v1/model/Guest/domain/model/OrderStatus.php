<?php 

namespace model\Guest\domain\model;

use MyCLabs\Enum\Enum;

class OrderStatus extends enum {
    const Active = 1;
    const Cancelled = 2;
    const CheckedIn = 3;
    const CheckedOut = 4;
}

?>