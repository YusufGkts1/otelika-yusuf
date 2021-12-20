<?php

namespace model\Alarm\domain\model;

use \model\common\IComparable;

class AlarmId implements IComparable
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
