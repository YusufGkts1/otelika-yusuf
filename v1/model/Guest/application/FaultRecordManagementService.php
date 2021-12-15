<?php

namespace model\Guest\application;

use model\common\ApplicationService;
use model\Guest\domain\model\GuestId;
use model\Guest\domain\model\IFaultRecordRepository;
use model\Guest\domain\model\IGuestRepository;
use model\Guest\domain\model\ModuleId;
use model\Guest\domain\model\ProductId;

class FaultRecordManagementService extends ApplicationService{

    function __construct(private IGuestRepository $guests, private IFaultRecordRepository $fault_records){}

    public function createFaultRecord(ProductId $broken_item_id, string $fault_note){

        $id = $this->fault_records->nextId();

        $guest = $this->guests->find($this->guestId());

        $fault_record = $guest->sendFaultRecord($id, $broken_item_id, $fault_note);

        $this->process($fault_record, $this->fault_records);
 
    	return $id->getId();
    }
    
    protected function guestId() : GuestId {
        return new GuestId($this->identity_provider->identity());
    }
}