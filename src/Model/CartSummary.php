<?php

namespace App\Model;
final class CartSummary
{
    /** @param CartItem[] $items */
    public function __construct(public readonly array $items) {}

    public function getTotal(): float
    {
        return array_reduce($this->items, fn($carry, $item) => $carry + $item->getSubtotal(), 0.0);
    }
}
