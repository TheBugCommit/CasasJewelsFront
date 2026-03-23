<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CartSessionService;
use App\UseCase\Cart\GetCartSummaryUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    public function __construct(
        private readonly GetCartSummaryUseCase $getCartSummary,
        private readonly CartSessionService $cartSession
    ) {}

    #[Route('', name: 'frontend_cart_index', methods: ['GET'])]
    public function index(): Response
    {
        $summary = $this->getCartSummary->execute();

        return $this->render('cart/index.html.twig', [
            'items' => $summary['items'],
            'total' => $summary['total'],
        ]);
    }

    #[Route('/add', name: 'frontend_cart_add', methods: ['POST'])]
    public function add(Request $request): Response
    {
        $productId = $request->request->get('product_id');
        $quantity = (int) $request->request->get('quantity', 1);

        $this->cartSession->addItem($productId, $quantity);
        $this->addFlash('success', 'Producte afegit al carretó correctament.');

        return $this->redirectToRoute('frontend_cart_index');
    }

    #[Route('/remove/{id}', name: 'frontend_cart_remove', methods: ['POST'])]
    public function remove(string $id): Response
    {
        $this->cartSession->removeItem($id);
        $this->addFlash('success', 'Producte eliminat del carretó.');

        return $this->redirectToRoute('frontend_cart_index');
    }
}
