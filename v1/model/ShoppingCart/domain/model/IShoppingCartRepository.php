<?php

namespace model\ShoppingCartItem\domain\model;

use model\common\Entity;
use model\common\IPersistenceProvider;

interface IShoppingCartRepository extends IPersistenceProvider
{
    public function find(ShoppingCartId $id) : ?ShoppingCart;

    public function save(Entity $entity);

    public function remove(ShoppingCartId $id, ProductId $product_id);

    public function nextId():ShoppingCartId;

    public function findcartByGuestId(GuestId $guest_id) : ?ShoppingCart;

    public function findShoppingCartItem(ShoppingCartId $id, ProductId $cart_item_id) : ?ShoppingCartItem;


}
