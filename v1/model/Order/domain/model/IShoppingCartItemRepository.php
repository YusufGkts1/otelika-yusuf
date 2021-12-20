<?php

namespace model\Order\domain\model;

use model\common\domain\model\ProductId;
use model\common\Entity;
use model\common\IPersistenceProvider;

interface IShoppingCartItemRepository extends IPersistenceProvider
{
    public function find(ShoppingCartItemId $id) : ?ShoppingCartItem;

    public function save(Entity $entity);

    public function remove(ShoppingCartItemId $id, ProductId $product_id);

    public function nextId():ShoppingCartItemId;


}
