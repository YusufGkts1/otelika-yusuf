<?php

namespace model\Guest\domain\model;

use model\common\Entity;
use model\common\IPersistenceProvider;

interface IAlarmRepository extends IPersistenceProvider
{
    public function find(cartId $id) : ?cart;

    public function save(Entity $entity);

    public function remove(string $id);

    public function nextId():cartId;

    public function findcartByGuestId(GuestId $guest_id) : ?cart;

    public function findProductIncart(ProductId $cart_item_id) : ?Product;


}
