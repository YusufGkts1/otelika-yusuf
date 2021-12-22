<?php

namespace model\InhouseReservation\application;

use DateTime;
use model\common\ApplicationService;
use model\common\domain\model\IGuestRepository;
use model\InhouseReservation\domain\model\IInhouseServiceRepository;
use model\InhouserReservation\domain\model\InhouseServiceId;

class InhouseReservationManagementService extends ApplicationService{

    function __construct(private IInhouseServiceRepository $inhouse_services, Private IGuestRepository $guests ){}

    public function createReservation(InhouseServiceId $inhouse_service_id, DateTime $reservation_date, int $number_of_people){

        $inhouse_service = $this->inhouse_services->find($inhouse_service_id);
    }
}