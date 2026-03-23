<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class CartSessionService
{
    public function __construct(private readonly RequestStack $requestStack) {}

    public function getCart(): array
    {
        return $this->requestStack->getSession()->get('cart', []);
    }

    public function addItem(string $productId, int $quantity): void
    {
        $session = $this->requestStack->getSession();
        $cart = $this->getCart();

        $cart[$productId] = ($cart[$productId] ?? 0) + $quantity;

        $session->set('cart', $cart);
    }

    public function removeItem(string $productId): void
    {
        $session = $this->requestStack->getSession();
        $cart = $this->getCart();

        unset($cart[$productId]);
        $session->set('cart', $cart);
    }

    public function clear(): void
    {
        $this->requestStack->getSession()->remove('cart');
    }
}
