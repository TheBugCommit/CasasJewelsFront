<?php

namespace App\Model;
use Sce\CasasJewelsVendor\DTO\Entity\ProductDTO;

final class CartItem
{
    public function __construct(
        public readonly ProductDTO $product,
        public readonly int $quantity
    ) {}

    public function getSubtotal(): float
    {
        return $this->product->price * $this->quantity;
    }
}
