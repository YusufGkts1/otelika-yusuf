<?php

namespace model\common\domain\model;

use model\common\Entity;
use model\Order\domain\model\CategoryId;
use model\Order\domain\model\ProductStatus;

class Product extends Entity
{
    function __construct(
        private ServiceModuleId $module_id,
        private ?CategoryId $category_id,
        private ProductId $id,
        private string $product_name,
        private string $description,
        private int $stock, 
        private float $price,
        private ProductStatus $status
    ){}

    //getter

    public function productId(){
        return $this->id;
    }

    public function moduleId(){
        return $this->module_id;
    }

    public function categoryId(){
        return $this->category_id;
    }

    public function calculatePrice($quantity){

        if(!$quantity){

            return $this->price;
        }

        $total_price = $this->price * $quantity;

        return $total_price;
    }


    



    public function totalPrice(float $quantity):float {
        $total_price = $quantity * $this->price;

        return $total_price;
    }
}
