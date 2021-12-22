<?php

namespace model\Order\application;

use DateTime;
use model\common\ApplicationService;
use model\common\domain\model\GuestId;
use model\common\domain\model\IGuestRepository;
use model\common\domain\model\IProductRepostitory;
use model\common\domain\model\IServiceModuleRepository;
use model\common\domain\model\ProductId;
use model\Order\domain\model\IOrderRepository;
use model\Order\domain\model\Order;
use model\Order\domain\model\OrderId;
use model\Order\domain\model\OrderStatus;

class OrderManagementService extends ApplicationService{

    function __construct(private IOrderRepository $orders, private IGuestRepository $guests, private IServiceModuleRepository $modules, private IProductRepostitory $products){}

    
    public function cancelOrder(OrderId $order_id, ?ProductId $product_id, int $status){

        $order = $this->existingOrder($order_id);

        if(new  OrderStatus($status) == OrderStatus::Cancelled()){

            if(!$product_id){
                $order->cancelAllCart();
            }
            $single_order = 


        }
       
     

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