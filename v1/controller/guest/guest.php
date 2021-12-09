<?php

use model\Guest\application\BasketManagementService;
use model\Guest\application\BasketQueryService;
use model\Guest\application\GuestQueryService;
use model\Guest\application\OrderManagementService;
use model\Guest\application\OrderQueryService;
use model\Guest\application\RoomItemQueryService;

class ControllerGuest extends RestEndpoint{

    protected function get(){

        if(!$this->uriAt(0))
            $this->getHomePageCategories();
        
        if('profile' == $this->uriAt(0)){
            if('order' == $this->uriAt(1)){
                if($this->uriAt(2)){
                    $this->getGuestSelfOrder();
                }
                $this->fetchGuestSelfOrders();
            }
            $this->getGuestProfile();
        }
         
        if('basket' == $this->uriAt(0)){
            if($this->uriAt(1)){
                $this->getSingleItemFromBasket();
            }
            $this->getBasket(); 
        }

        if('fault_record' == $this->uriAt(0))
            if($this->uriAt(1)){
                $this->getRoomItem();
            }
            $this->fetchRoomItems();
    }

    protected function post(){
        if('basket' == $this->uriAt(0))
            $this->confirmBasket();

        if('basket' != $this->uriAt(0))
            if($this->uriAt(1)){
                $this->addToBasket();
            }

        if('taxi' == $this->uriAt(0))
            $this->callTaxi();

        if('wake_up_service' == $this->uriAt(0))
            $this->createWakeUpAlarm();

        if('fault_record' == $this->uriAt(0)){
            if($this->uriAt(1))
                $this->sendFaultRecord();
        }
    }

    protected function patch(){
        if('profile' == $this->uriAt(0)){
            if('order' == $this->uriAt(1)){
                if($this->uriAt(2)){
                    $this->cancelOrder();
                }
            }
        }

        if('basket' == $this->uriAt(0)){
            if($this->uriAt(1)){
                $this->changeProductPieceFromBasket();
            }
        }
    }

    protected function delete(){
        if('basket' == $this->uriAt(0)){
            if($this->uriAt(1)){
                $this->deleteItemFromBasket();
            }
        }
       
    }

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
    $this->load->module('Guest');

    $this->order_management_service = $this->module_guest->service('OrderManagementService');

    return $this->order_management_service;
    }

    private function basketManagementService(): BasketManagementService
    {
    $this->load->module('Guest');

    $this->basket_management_service = $this->module_guest->service('BasketManagementService');

    return $this->basket_management_service;
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

    private function basketQueryService(): BasketQueryService
    {
        if ($this->basket_query_service)
            return $this->basket_query_service;

        $this->load->module('Guest');

        $this->basket_query_service = $this->module_guest->service('BasketQueryService');

        return $this->basket_query_service;
    }

    private function roomItemQueryService(): RoomItemQueryService
    {
        if ($this->room_item_query_service)
            return $this->room_item_query_service;

        $this->load->module('Guest');

        $this->room_item_query_service = $this->module_guest->service('RoomItemQueryService');

        return $this->room_item_query_service;
    }

    private function getHomePageCategories(){}

    private function getGuestProfile(){

        $profile = $this->guestQueryService()->getProfile($this->queryServiceQueryObject());
        
        $this->success($profile);
    }

    private function fetchGuestSelfOrders(){
        
        $orders = $this->orderQueryService()->fetchSelfOwnedOrders($this->queryServiceQueryObject());

		$this->success($orders);
    }

    private function getGuestSelfOrder(){

        $order = $this->orderQueryService()->getGuestSingleOrderById($this->uriAt(2),$this->queryServiceQueryObject());
        
        $this->success($order);
    }

    private function getBasket(){

        $basket = $this->basketQueryService()->fetchSelfOwnedBasketItems($this->queryServiceQueryObject());

		$this->success($basket);
    }

    private function getSingleItemFromBasket(){

        $basket_item = $this->basketQueryService()->getSelfOwnedBasketItem($this->uriAt(1), $this->queryServiceQueryObject());

		$this->success($basket_item);
    }

    private function fetchRoomItems(){

        $room_items = $this->roomItemQueryService()->fetchRoomItems($this->queryServiceQueryObject());

		$this->success($room_items);
    }

    private function getRoomItem(){

        $room_item = $this->roomItemQueryService()->getRoomItemById($this->queryServiceQueryObject());

		$this->success($room_item);
    }

    private function confirmBasket(){}

    private function addToBasket(){}

    private function cancelOrder(){

        $this->orderManagementService()->cancelOrder(
            $this->uriAt(2),
            $this->getAttr('status',true)
            );
    
            $orders = $this->orderQueryService()->fetchGuestSelfOrders($this->queryServiceQueryObject());
    
            $this->success($orders);
    }

    private function changeProductPieceFromBasket(){ //Ürün adedi değiştirilirken hem stoktan güncelleme olacak gem basket tarafında güncelleme olacak. Bu güncellemenin yapılacağı fonksiyon ürün üzerinden mi olacak?

        $this->basketManagementService()->changePieceOfProduct(
            $this->uriAt(1),
            $this->getAttr('piece',true));

            
        $basket = $this->basketQueryService()->fetchSelfOwnedBasketItems($this->queryServiceQueryObject());
  
        $this->success($basket);
    }

    private function deleteItemFromBasket(){

        $this->basketManagementService()->deleteItem($this->uriAt(1));
  
        $this->noContent();
    }

    private function sendFaultRecord(){

        $this->orderManagementService()->createFaultRecord(
            $this->getAttr('product_id'),
            $this->getAttr('fault_note')
        );
    }

    private function callTaxi(){

        $this->orderManagementService()->callTaxi(
            $this->uriAt(0),
            $this->getAttr('countdown'),
            $this->getAttr('order_note')
            );
        
        $this->noContent();
    }

    private function createWakeUpAlarm(){

        $this->orderManagementService()->wakeUpService(
            $this->getAttr('wake_up_time')
        );

        $this->noContent();
    }


}