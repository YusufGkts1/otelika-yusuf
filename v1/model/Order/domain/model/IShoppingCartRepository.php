<?php

namespace model\Order\domain\model;

use model\common\domain\model\GuestId;
use model\common\domain\model\ProductId;
use model\common\Entity;
use model\common\IPersistenceProvider;

interface IShoppingCartRepository extends IPersistenceProvider
{
    public function find(GuestId $guest_id) : ?ShoppingCartId;

    public function save(Entity $entity);

    public function remove(ShoppingCartId $id, ProductId $product_id);

    public function nextId():ShoppingCartId;


}
