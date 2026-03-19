<?php

declare(strict_types=1);

namespace App\Controller;

use Sce\CasasJewelsVendor\DTO\ErrorResponseDTO;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductDetailRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductListRequestDTO;
use Sce\CasasJewelsVendor\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CatalogController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $vendorProductRepository
    ) {}

    #[Route('/', name: 'frontend_catalog', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $limit = $request->query->getInt('limit', 12);
        $offset = $request->query->getInt('page', 1);

        $requestDTO = new GetProductListRequestDTO($limit, $offset);

        // Cridem al TEU vendor!
        $responseDTO = $this->vendorProductRepository->getProductList($requestDTO);

        if ($responseDTO instanceof ErrorResponseDTO) {
            $this->addFlash('error', 'No hem pogut carregar el catàleg.');
            $products = [];
            $totalPages = 1;
        } else {
            $products = $responseDTO->items;
            $totalPages = $responseDTO->totalPages;
        }

        return $this->render('catalog/index.html.twig', [
            'products'    => $products,
            'currentPage' => $offset,
            'totalPages'  => $totalPages,
            'limit'       => $limit,
        ]);
    }

    #[Route('/product/{id}', name: 'frontend_product_detail', methods: ['GET'])]
    public function detail(string $id): Response
    {
        $requestDTO = new GetProductDetailRequestDTO($id);
        $responseDTO = $this->vendorProductRepository->getProductDetail($requestDTO);

        if ($responseDTO instanceof ErrorResponseDTO) {
            throw $this->createNotFoundException('Producte no trobat.');
        }

        return $this->render('catalog/detail.html.twig', [
            'product' => $responseDTO->data,
        ]);
    }
}
