<?php

namespace App\UseCase\Cart;

use App\Model\CartItem;
use App\Model\CartSummary;
use App\Service\CartSessionService;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductsByIdsRequestDTO;
use Sce\CasasJewelsVendor\Repository\ProductRepository;
use Sce\CasasJewelsVendor\DTO\ErrorResponseDTO;

class GetCartSummaryUseCase
{
    public function __construct(
        private readonly CartSessionService $cartSession,
        private readonly ProductRepository $vendorProductRepository
    ) {}

    public function execute(): CartSummary
    {
        $cart = $this->cartSession->getCart();

        if (empty($cart)) {
            return new CartSummary([]);
        }
        $productIds = array_keys($cart);
        $request = new GetProductsByIdsRequestDTO($productIds);
        $response = $this->vendorProductRepository->getProductsByIds($request);

        if ($response instanceof ErrorResponseDTO || empty($response->items)) {
            return new CartSummary([]);
        }

        $productsById = [];
        foreach ($response->items as $productDTO) {
            $productsById[$productDTO->id] = $productDTO;
        }

        $cartItems = [];
        foreach ($cart as $productId => $quantity) {
            if (isset($productsById[$productId])) {
                $cartItems[] = new CartItem(
                    product: $productsById[$productId],
                    quantity: $quantity
                );
            }
        }

        return new CartSummary($cartItems);
    }
}
