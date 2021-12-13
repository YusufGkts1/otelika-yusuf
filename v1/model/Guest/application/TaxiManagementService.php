<?php

namespace model\Guest\application;

use model\common\ApplicationService;
use model\Guest\domain\model\GuestId;
use model\Guest\domain\model\IGuestRepository;
use model\Guest\domain\model\ITaxiRepository;

class TaxiManagementService extends ApplicationService{

    function __construct(private IGuestRepository $guests, private ITaxiRepository $taxi_calls){}

    public function callTaxi(int $countdown, string $guest_note)
    {
        $id = $this->taxi_calls->nextId();

        $guest = $this->guests->find($this->guestId());

        $taxi_call = $guest->orderTaxi($id, $countdown, $guest_note);

        $this->process($taxi_call, $this->taxi_calls);

    	return $id->getId();
    }

    protected function guestId() : GuestId {
        return new GuestId($this->identity_provider->identity());
    }
}