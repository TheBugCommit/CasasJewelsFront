<?php

namespace App\UseCase\Cart;

use App\Service\CartSessionService;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductDetailRequestDTO;
use Sce\CasasJewelsVendor\DTO\ErrorResponseDTO;
use Sce\CasasJewelsVendor\Repository\ProductRepository;

class GetCartSummaryUseCase
{
    public function __construct(
        private readonly CartSessionService $cartSession,
        private readonly ProductRepository $vendorProductRepository
    ) {}

    /**
     * @return array{items: array, total: float}
     */
    public function execute(): array
    {
        $cart = $this->cartSession->getCart();
        $cartItems = [];
        $total = 0.0;

        foreach ($cart as $productId => $quantity) {
            $requestDTO = new GetProductDetailRequestDTO($productId);
            $responseDTO = $this->vendorProductRepository->getProductDetail($requestDTO);

            if (!$responseDTO instanceof ErrorResponseDTO) {
                $product = $responseDTO->data;
                $subtotal = $product->price * $quantity;
                $total += $subtotal;

                $cartItems[] = [
                    'product'  => $product,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal
                ];
            }
        }

        return [
            'items' => $cartItems,
            'total' => $total,
        ];
    }
}
