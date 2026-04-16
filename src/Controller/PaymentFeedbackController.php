<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/checkout')]
class PaymentFeedbackController extends AbstractController
{
    #[Route('/success', name: 'frontend_checkout_success', methods: ['GET'])]
    public function success(): Response
    {
        return $this->render('checkout/success.html.twig');
    }

    #[Route('/cancel', name: 'frontend_checkout_cancel', methods: ['GET'])]
    public function cancel(): Response
    {
        return $this->render('checkout/cancel.html.twig');
    }
}
