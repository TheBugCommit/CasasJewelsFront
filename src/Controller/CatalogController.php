<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\CatalogFetchException;
use App\UseCase\Catalog\GetCatalogListUseCase;
use App\UseCase\Catalog\GetProductDetailUseCase;
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
        $page  = max(1, $request->query->getInt('page', 1));
        $limit = max(1, min($request->query->getInt('limit', 12), 48));

        try {
            $responseDTO = $this->getCatalogList->execute($limit, $page);

            $products   = $responseDTO->items;
            $totalPages = $responseDTO->totalPages;

        } catch (CatalogFetchException $e) {
            $this->addFlash('error', $e->getMessage());
            $products   = [];
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
