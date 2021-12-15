<?php

namespace model\Guest\domain\model;

use model\common\Entity;

class ShoppingCartItem extends Entity
{
    function __construct(
        ShoppingCartId $id,

    ){}

    public function changeQuantityOfcartItem(ProductId $cart_item_id, float $quantity){

    
    }

    public function removeShoppingCartItem(){
        $this->_remove();
    }
}
