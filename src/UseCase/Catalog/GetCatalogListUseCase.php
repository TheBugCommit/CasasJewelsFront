<?php

namespace App\UseCase\Catalog;

use App\Exception\CatalogFetchException;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductListRequestDTO;
use Sce\CasasJewelsVendor\DTO\ErrorResponseDTO;
use Sce\CasasJewelsVendor\Repository\ProductRepository;

class GetCatalogListUseCase
{
    public function __construct(private readonly ProductRepository $vendorProductRepository) {}

    /**
     * @return array{items: array, totalPages: int, total: int}
     * @throws CatalogFetchException
     */
    public function execute(int $limit, int $page): array
    {
        $requestDTO = new GetProductListRequestDTO($limit, $page);
        $responseDTO = $this->vendorProductRepository->getProductList($requestDTO);

        if ($responseDTO instanceof ErrorResponseDTO) {
            throw new CatalogFetchException('No hem pogut carregar el catàleg: ' . $responseDTO->message);
        }

        return [
            'items'      => $responseDTO->items,
            'totalPages' => $responseDTO->totalPages,
            'total'      => $responseDTO->total,
        ];
    }
}
