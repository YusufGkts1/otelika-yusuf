<?php

namespace model\Guest\domain\model;

use model\common\Entity;

class OrderProducts extends Entity
{
    function __construct(
        ModuleId $category_id,
        ProductId $product_id,
        private float $price,
        private int $quantity,
    ){}

}
