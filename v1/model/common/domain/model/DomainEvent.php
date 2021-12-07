<?php 

namespace model\common\domain\model;

abstract class DomainEvent implements \JsonSerializable {
    
    private int $event_version;
    private \DateTime $occurred_on;

    function __construct() {
        $this->event_version = 1;
        $this->occurred_on = new \DateTime();
    }
    
    public function eventVersion() : int {
        return $this->event_version;
    }

    public function occurredOn() : \DateTime {
        return $this->occurred_on;
    }

    public function data() : array {
        return $this->jsonSerialize();
    }
}

?>