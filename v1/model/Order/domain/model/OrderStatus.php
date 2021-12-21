<?php 

namespace model\Order\domain\model;

use MyCLabs\Enum\Enum;

class OrderStatus extends enum {
    const Ordered = 1;
    const InProgress = 2;
    const CancelRequest = 3;
    const Canceled = 4;
    const Completed = 5;

}

?>