<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\CheckoutProcessException;
use App\UseCase\Cart\GetCartSummaryUseCase;
use App\UseCase\Checkout\ProcessCheckoutUseCase;
use Sce\CasasJewelsVendor\DTO\ErrorResponseDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/checkout')]
class CheckoutController extends AbstractController
{
    public function __construct(
        private readonly GetCartSummaryUseCase $getCartSummary,
        private readonly ProcessCheckoutUseCase $processCheckout
    ) {}

    #[Route('', name: 'frontend_checkout_index', methods: ['GET'])]
    public function index(): Response
    {
        $summary = $this->getCartSummary->execute();

        if (empty($summary['items'])) {
            return $this->redirectToRoute('frontend_catalog');
        }

        return $this->render('checkout/index.html.twig', [
            'total' => $summary['total'],
        ]);
    }

    #[Route('/process', name: 'frontend_checkout_process', methods: ['POST'])]
    public function process(Request $request): Response
    {
        $clientName = $request->request->get('clientName');
        $clientEmail = $request->request->get('clientEmail');

        try {
            $result = $this->processCheckout->execute($clientName, $clientEmail);
        } catch (CheckoutProcessException $e) {
            $this->addFlash('error', 'Error en tramitar la comanda: ' . $e->getMessage());
            return $this->redirectToRoute('frontend_checkout_index');
        }

        return $this->redirect($result);
    }
}
