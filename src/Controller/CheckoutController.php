<?php

declare(strict_types=1);

namespace App\Controller;

use Sce\CasasJewelsVendor\DTO\ErrorResponseDTO;
use Sce\CasasJewelsVendor\DTO\Requests\CreateOrder\CreateOrderRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\CreateOrder\OrderLineRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductDetailRequestDTO;
use Sce\CasasJewelsVendor\Repository\OrderRepository;
use Sce\CasasJewelsVendor\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/checkout')]
class CheckoutController extends AbstractController
{
    public function __construct(
        private readonly OrderRepository $vendorOrderRepository,
        private readonly ProductRepository $vendorProductRepository,
        private readonly RequestStack $requestStack
    ) {}

    #[Route('', name: 'frontend_checkout_index', methods: ['GET'])]
    public function index(): Response
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        // Si el carretó està buit, el tornem al catàleg
        if (empty($cart)) {
            return $this->redirectToRoute('frontend_catalog');
        }

        // Calculem el total per mostrar-ho al resum
        $total = 0;
        foreach ($cart as $productId => $quantity) {
            $responseDTO = $this->vendorProductRepository->getProductDetail(new GetProductDetailRequestDTO($productId));
            if (!$responseDTO instanceof ErrorResponseDTO) {
                $total += $responseDTO->data->price * $quantity;
            }
        }

        return $this->render('checkout/index.html.twig', [
            'total' => $total,
        ]);
    }

    #[Route('/process', name: 'frontend_checkout_process', methods: ['POST'])]
    public function process(Request $request): Response
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        if (empty($cart)) {
            return $this->redirectToRoute('frontend_catalog');
        }

        // 1. Recollim les dades del client del formulari
        $clientName = $request->request->get('clientName');
        $clientEmail = $request->request->get('clientEmail');

        // 2. Creem les línies de la comanda pel DTO
        $lines = [];
        foreach ($cart as $productId => $quantity) {
            $lines[] = new OrderLineRequestDTO($productId, $quantity);
        }

        // 3. Creem la petició i truquem a l'API a través del Vendor
        $orderRequest = new CreateOrderRequestDTO($clientName, $clientEmail, $lines);
        $response = $this->vendorOrderRepository->createOrder($orderRequest);

        // Si l'API falla, mostrem error
        if ($response instanceof ErrorResponseDTO) {
            $this->addFlash('error', 'Error en tramitar la comanda: ' . $response->message);
            return $this->redirectToRoute('frontend_checkout_index');
        }

        // 4. Si tot va bé, BUIDEM EL CARRETÓ
        $session->remove('cart');

        // 5. Redirigim el client a l'enllaç de pagament de Paypal que ens ha retornat l'API
        //return $this->redirect($response->paymentUrl);
        return $this->redirect('/');
    }
}
