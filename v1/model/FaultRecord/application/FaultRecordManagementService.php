<?php

namespace model\FaultRecord\application;

use model\common\ApplicationService;
use model\common\domain\model\GuestId;
use model\common\domain\model\IGuestRepository;
use model\common\domain\model\IProductRepository;
use model\common\domain\model\ProductId;
use model\FaultRecord\domain\model\IFaultRecordRepository;

class FaultRecordManagementService extends ApplicationService{

    function __construct(private IGuestRepository $guests, private IFaultRecordRepository $fault_records, private IProductRepository $products){}

    public function createFaultRecord(ProductId $broken_item_id, string $fault_note){

        $id = $this->fault_records->nextId();

        $product = $this->products->find($broken_item_id);

        $guest = $this->guests->find($this->guestId());

        $fault_record = $guest->sendFaultRecord($id, $product, $fault_note);

        $this->process($fault_record, $this->fault_records);
 
    	return $id->getId();
    }
    
    protected function guestId() : GuestId {
        return new GuestId($this->identity_provider->identity());
    }
}