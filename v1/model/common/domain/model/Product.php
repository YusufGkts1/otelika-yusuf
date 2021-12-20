<?php

namespace model\common\domain\model;

use model\common\Entity;

class Product extends Entity
{
    function __construct(
        private float $price
    ){}


    public function totalPrice(float $quantity):float {
        $total_price = $quantity * $this->price;

        return $total_price;
    }
}
