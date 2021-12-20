<?php

namespace model\Alarm\application;

use model\common\ApplicationService;
use model\Guest\domain\model\GuestId;
use model\Guest\domain\model\IAlarmRepository;
use model\Guest\domain\model\IGuestRepository;

class AlarmManagementService extends ApplicationService{

    function __construct(private IGuestRepository $guests, private IAlarmRepository $alarms){}

    public function wakeUpService(\DateTime $wake_up_time){

        $id = $this->alarms->nextId();

        $guest = $this->guests->find($this->guestId());

        $alarm = $guest->wakeUpAlarm($id, $wake_up_time);

        $this->process($alarm, $this->alarms);
 
    	return $id->getId();
    }
    

    protected function guestId() : GuestId {
        return new GuestId($this->identity_provider->identity());
    }
}