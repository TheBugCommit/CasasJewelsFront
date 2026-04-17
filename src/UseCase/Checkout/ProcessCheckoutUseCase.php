<?php

namespace App\UseCase\Checkout;

use App\Exception\CheckoutProcessException;
use App\UseCase\Cart\GetCartSummaryUseCase;
use Sce\CasasJewelsVendor\DTO\Requests\CreateOrder\CreateOrderRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\CreateOrder\OrderLineRequestDTO;
use Sce\CasasJewelsVendor\DTO\ErrorResponseDTO;
use Sce\CasasJewelsVendor\Repository\OrderRepository;

class ProcessCheckoutUseCase
{
    public function __construct(
        private readonly GetCartSummaryUseCase $getCartSummary,
        private readonly OrderRepository       $vendorOrderRepository
    ) {}

    /**
     * @throws CheckoutProcessException
     */
    public function execute(string $clientName, string $clientEmail): string
    {
        $summary = $this->getCartSummary->execute();

        if (empty($summary->items)) {
            throw new CheckoutProcessException('El carretó està buit o els productes ja no estan disponibles.');
        }

        $lines = [];
        foreach ($summary->items as $item) {
            $lines[] = new OrderLineRequestDTO(
                productId: $item->product->id,
                quantity:  $item->quantity
            );
        }

        $orderRequest = new CreateOrderRequestDTO($clientName, $clientEmail, $lines);
        $response = $this->vendorOrderRepository->createOrder($orderRequest);

        if ($response instanceof ErrorResponseDTO) {
            throw new CheckoutProcessException($response->message);
        }

        return $response->paymentUrl;
    }
}
