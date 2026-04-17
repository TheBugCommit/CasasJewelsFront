<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\CheckoutProcessException;
use App\UseCase\Cart\GetCartSummaryUseCase;
use App\UseCase\Checkout\ProcessCheckoutUseCase;
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

        if (empty($summary->items)) {
            return $this->redirectToRoute('frontend_catalog');
        }

        return $this->render('checkout/index.html.twig', [
            'total' => $summary->getTotal(),
        ]);
    }

    #[Route('/process', name: 'frontend_checkout_process', methods: ['POST'])]
    public function process(Request $request): Response
    {
        $clientName  = trim($request->request->getString('clientName'));
        $clientEmail = trim($request->request->getString('clientEmail'));

        if ($clientName === '' || $clientEmail === '') {
            $this->addFlash('error', 'El nom i el correu electrònic són obligatoris per tramitar la comanda.');
            return $this->redirectToRoute('frontend_checkout_index');
        }

        if (!filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('error', 'El correu electrònic no té un format vàlid.');
            return $this->redirectToRoute('frontend_checkout_index');
        }

        try {
            $paymentUrl = $this->processCheckout->execute($clientName, $clientEmail);
        } catch (CheckoutProcessException $e) {
            $this->addFlash('error', 'Error en tramitar la comanda: ' . $e->getMessage());
            return $this->redirectToRoute('frontend_checkout_index');
        } catch (\Exception $e) {
            $this->addFlash('error', 'S\'ha produït un error inesperat. Torna-ho a provar.');
            return $this->redirectToRoute('frontend_checkout_index');
        }

        return $this->redirect($paymentUrl);
    }
}
