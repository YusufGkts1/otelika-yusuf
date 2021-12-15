<?php

namespace model\Guest\application;

use DateTime;
use model\Guest\domain\model\IOrderRepository;
use model\common\ApplicationService;
use model\Guest\domain\model\GuestId;
use model\Guest\domain\model\IGuestRepository;
use model\Guest\domain\model\IModuleRepository;
use model\Guest\domain\model\IProductRepostitory;
use model\Guest\domain\model\ModuleId;
use model\Guest\domain\model\Order;
use model\Guest\domain\model\OrderId;
use model\Guest\domain\model\OrderStatus;
use model\Guest\domain\model\ProductId;

class OrderManagementService extends ApplicationService{

    function __construct(private IOrderRepository $orders, private IGuestRepository $guests, private IModuleRepository $modules, private IProductRepostitory $products){}

    
    public function cancelOrder(OrderId $id, int $status){

        $order = $this->existingOrder($id);

        if(new  OrderStatus($status) == OrderStatus::Cancelled())
            $order->cancel();

        $this->process($order, $this->orders);
    }

    private function existingOrder(OrderId $id) : Order {
        $order = $this->orders->find(new OrderId ($id));
        if(null == $order)
            throw new \NotFoundException('Order is not found');

        return $order;
    }

    protected function guestId() : GuestId {
        return new GuestId($this->identity_provider->identity());
    }
}