<?php

namespace model\Guest\domain\model;

use model\common\Entity;

class Order extends Entity
{
    function __construct(){}


    public function cancel(){

        $canceled = OrderStatus::Cancelled();
        $this->status = $canceled;
    
        return $this->status;
    
        }
}
