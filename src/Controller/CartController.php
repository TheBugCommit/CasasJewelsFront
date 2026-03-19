<?php

declare(strict_types=1);

namespace App\Controller;

use Sce\CasasJewelsVendor\DTO\ErrorResponseDTO;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductDetailRequestDTO;
use Sce\CasasJewelsVendor\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $vendorProductRepository,
        private readonly RequestStack $requestStack
    ) {}

    // 1. Ver el carrito
    #[Route('', name: 'frontend_cart_index', methods: ['GET'])]
    public function index(): Response
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        $cartItems = [];
        $total = 0;

        // Recorremos los IDs guardados en sesión y pedimos los detalles a tu Vendor API
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

        return $this->render('cart/index.html.twig', [
            'items' => $cartItems,
            'total' => $total,
        ]);
    }

    #[Route('/add', name: 'frontend_cart_add', methods: ['POST'])]
    public function add(Request $request): Response
    {
        $productId = $request->request->get('product_id');
        $quantity = (int) $request->request->get('quantity', 1);

        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId] += $quantity;
        } else {
            $cart[$productId] = $quantity;
        }

        $session->set('cart', $cart);

        $this->addFlash('success', 'Producto añadido al carrito correctamente.');

        return $this->redirectToRoute('frontend_cart_index');
    }

    #[Route('/remove/{id}', name: 'frontend_cart_remove', methods: ['POST'])]
    public function remove(string $id): Response
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            $session->set('cart', $cart);
            $this->addFlash('success', 'Producto eliminado del carrito.');
        }

        return $this->redirectToRoute('frontend_cart_index');
    }
}
