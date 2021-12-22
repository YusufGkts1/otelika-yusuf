<?php

use model\Guest\application\GuestQueryService;
use model\Guest\application\ModuleQueryService;
use model\Guest\application\OrderQueryService;
use model\Order\application\OrderManagementService;

class ControllerGuest extends RestEndpoint{

    protected function get(){

        if(!$this->uriAt(0))
            $this->fetchHomePageModules();
        
        if('profile' == $this->uriAt(0)){
            if('order' == $this->uriAt(1)){
                if($this->uriAt(2)){
                    $this->getGuestSelfOrder();
                }
                $this->fetchGuestSelfOrders();
            }
            $this->getGuestProfile();
        }
    }

    protected function post(){}

    protected function patch(){
        if('profile' == $this->uriAt(0)){
            if('order' == $this->uriAt(1)){
                if($this->uriAt(2)){
                    $this->cancelOrder();
                }
            }
        }
    }

    protected function delete(){}

    protected function submoduleId(): int{
        return 0;
    }

    protected function filterSupportingFields(): array{
        return array();
    }

    protected function orderBySupportingFields(): array{
        return $this->filterSupportingFields();
    }

    private function orderManagementService(): OrderManagementService
    {
    $this->load->module('Order');

    $this->order_management_service = $this->module_order->service('OrderManagementService');

    return $this->order_management_service;
    }

    private function guestQueryService(): GuestQueryService
    {
        if ($this->guest_query_service)
            return $this->guest_query_service;

        $this->load->module('Guest');

        $this->guest_query_service = $this->module_guest->service('GuestQueryService');

        return $this->guest_query_service;
    }

    private function orderQueryService(): OrderQueryService
    {
        if ($this->order_query_service)
            return $this->order_query_service;

        $this->load->module('Guest');

        $this->order_query_service = $this->module_guest->service('OrderQueryService');

        return $this->order_query_service;
    }

    private function moduleQueryService(): ModuleQueryService
    {
        if ($this->module_query_service)
            return $this->module_query_service;

        $this->load->module('Guest');

        $this->module_query_service = $this->module_guest->service('ModuleQueryService');

        return $this->module_query_service;
    }

    private function fetchHomePageModules(){

        $modules = $this->moduleQueryService()->fetchModules($this->queryServiceQueryObject());
        
        $this->success($modules);
    }

    private function getGuestProfile(){

        $profile = $this->guestQueryService()->getProfile($this->queryServiceQueryObject());
        
        $this->success($profile);
    }

    private function fetchGuestSelfOrders(){
        
        $orders = $this->orderQueryService()->fetchGuestSelfOwnedOrders($this->queryServiceQueryObject());

		$this->success($orders);
    }

    private function getGuestSelfOrder(){

        $order = $this->orderQueryService()->getGuestSingleOrderById($this->uriAt(2),$this->queryServiceQueryObject());
        
        $this->success($order);
    }

    private function cancelOrder(){

        $this->orderManagementService()->cancelOrder(
            $this->getAttr('order_id'),
            $this->getAttr('product_id', true),
            $this->getAttr('status',false)
            );
    
            $orders = $this->orderQueryService()->fetchGuestSelfOrders($this->queryServiceQueryObject());
    
            $this->success($orders);
    }
}