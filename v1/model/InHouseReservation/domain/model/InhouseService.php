<?php

namespace model\InhouseReservation\domain\model;

use model\common\domain\model\ServiceModule;
use model\common\Entity;
use model\InhouserReservation\domain\model\InhouseServiceId;
use model\Order\domain\model\CategoryId;

class InhouseService extends Entity
{
    function __construct(
        private InhouseServiceId $id,
        private ServiceModule $module_id,
        private ?CategoryId $category_id,
        private int $quota,
        private array $days
    ){}

    public function getId(){
        return $this->id;
    }

}
