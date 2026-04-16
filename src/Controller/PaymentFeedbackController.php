<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CartSessionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/checkout')]
class PaymentFeedbackController extends AbstractController
{
    public function __construct(
        private readonly CartSessionService $cartSessionService
    ) {}
    #[Route('/success', name: 'frontend_checkout_success', methods: ['GET'])]
    public function success(): Response
    {
        $this->cartSessionService->clear();
        return $this->render('checkout/success.html.twig');
    }

    #[Route('/cancel', name: 'frontend_checkout_cancel', methods: ['GET'])]
    public function cancel(): Response
    {
        return $this->render('checkout/cancel.html.twig');
    }
}
