<?php

namespace model\Order\domain\model;

use \model\common\IComparable;

class CategoryId implements IComparable
{
    private string $id;

    function __construct($id){

        $this->id =  $id;
    }

    public function getId() : string{
        
        return $this->id;
    }

    public function equals($id): bool{

        return $this->id == $id->getId();
    }
}
