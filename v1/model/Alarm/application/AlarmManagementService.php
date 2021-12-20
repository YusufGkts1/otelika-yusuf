<?php

namespace model\Alarm\application;

use model\Alarm\domain\model\IAlarmRepository;
use model\common\ApplicationService;
use model\common\domain\model\GuestId;
use model\common\domain\model\IGuestRepository;

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