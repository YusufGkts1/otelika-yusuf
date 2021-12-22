<?php

namespace model\InhouseReservation\application;

use DateTime;
use model\common\ApplicationService;
use model\common\domain\model\GuestId;
use model\common\domain\model\IGuestRepository;
use model\InhouseReservation\domain\model\IInhouseReservationRepository;
use model\InhouseReservation\domain\model\IInhouseServiceRepository;
use model\InhouserReservation\domain\model\InhouseServiceId;

class InhouseReservationManagementService extends ApplicationService{

    function __construct(private IInhouseServiceRepository $inhouse_services, Private IGuestRepository $guests, private IInhouseReservationRepository $inhouse_reservations ){}

    public function createReservation(InhouseServiceId $inhouse_service_id, DateTime $reservation_date, int $number_of_people){

        $inhouse_service = $this->inhouse_services->find($inhouse_service_id);

        $inhouse_service;

        //Kota kontrolü gün kontrolü vs kontroller için Service içerisinde gidilecek

        $guest = $this->guests->find($this->guestId());


        $id = $this->inhouse_reservations->nextId();

        $inhouse_reservation = $guest->createInhouseReservation($id, $inhouse_service, $reservation_date, $number_of_people);
    }

    protected function guestId() : GuestId {
        return new GuestId($this->identity_provider->identity());
    }

}