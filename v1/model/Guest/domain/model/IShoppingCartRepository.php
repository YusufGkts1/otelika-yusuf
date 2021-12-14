<?php

namespace model\Guest\domain\model;

use model\common\Entity;
use model\common\IPersistenceProvider;

interface IShoppingCartRepository extends IPersistenceProvider
{
    public function find(ShoppingCartId $id) : ?ShoppingCart;

    public function save(Entity $entity);

    public function remove(string $id);

    public function nextId():ShoppingCartId;

    public function findcartByGuestId(GuestId $guest_id) : ?ShoppingCart;

    public function findProductIncart(ProductId $cart_item_id) : ?Product;


}
