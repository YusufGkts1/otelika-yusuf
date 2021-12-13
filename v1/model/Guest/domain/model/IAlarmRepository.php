<?php

namespace model\Guest\domain\model;

use model\common\Entity;
use model\common\IPersistenceProvider;

interface IAlarmRepository extends IPersistenceProvider
{
    public function find(BasketId $id) : ?Basket;

    public function save(Entity $entity);

    public function remove(string $id);

    public function nextId():BasketId;

    public function findBasketByGuestId(GuestId $guest_id) : ?Basket;

    public function findProductInBasket(ProductId $basket_item_id) : ?Product;


}
