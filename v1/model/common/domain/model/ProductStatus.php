<?php 

namespace model\Order\domain\model;

use MyCLabs\Enum\Enum;

class ProductStatus extends enum {
    const Active = 1;
    const Passive = 2;
}

?>