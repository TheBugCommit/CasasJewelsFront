<?php

namespace App\UseCase\Checkout;

use App\Service\CartSessionService;
use Sce\CasasJewelsVendor\DTO\Requests\CreateOrder\CreateOrderRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\CreateOrder\OrderLineRequestDTO;
use Sce\CasasJewelsVendor\DTO\ErrorResponseDTO;
use Sce\CasasJewelsVendor\Repository\OrderRepository;

class ProcessCheckoutUseCase
{
    public function __construct(
        private readonly CartSessionService $cartSession,
        private readonly OrderRepository $vendorOrderRepository
    ) {}

    public function execute(string $clientName, string $clientEmail): string|ErrorResponseDTO
    {
        $cart = $this->cartSession->getCart();

        if (empty($cart)) {
            return new ErrorResponseDTO(400, 'El carretó està buit.');
        }

        $lines = [];
        foreach ($cart as $productId => $quantity) {
            $lines[] = new OrderLineRequestDTO($productId, $quantity);
        }

        $orderRequest = new CreateOrderRequestDTO($clientName, $clientEmail, $lines);
        $response = $this->vendorOrderRepository->createOrder($orderRequest);

        if ($response instanceof ErrorResponseDTO) {
            return $response;
        }

        $this->cartSession->clear();

        return $response->paymentUrl;
    }
}
