<?php

declare(strict_types=1);

namespace App\Controller;

use App\UseCase\Catalog\GetCatalogListUseCase;
use App\UseCase\Catalog\GetProductDetailUseCase;
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
        private readonly GetCatalogListUseCase $getCatalogList,
        private readonly GetProductDetailUseCase $getProductDetail
    ) {}

    #[Route('/', name: 'frontend_catalog', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $limit = $request->query->getInt('limit', 12);
        $page = $request->query->getInt('page', 1);

        try {
            $data = $this->getCatalogList->execute($limit, $page);

            $products = $data['items'];
            $totalPages = $data['totalPages'];

        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
            $products = [];
            $totalPages = 1;
        }

        return $this->render('catalog/index.html.twig', [
            'products'    => $products,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'limit'       => $limit,
        ]);
    }

    #[Route('/product/{id}', name: 'frontend_product_detail', methods: ['GET'])]
    public function detail(string $id): Response
    {
        $product = $this->getProductDetail->execute($id);

        return $this->render('catalog/detail.html.twig', [
            'product' => $product,
        ]);
    }
}
