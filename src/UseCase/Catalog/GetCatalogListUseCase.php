<?php

namespace App\UseCase\Catalog;

use App\Exception\CatalogFetchException;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductListRequestDTO;
use Sce\CasasJewelsVendor\DTO\ErrorResponseDTO;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductListResponseDTO;
use Sce\CasasJewelsVendor\Repository\ProductRepository;

class GetCatalogListUseCase
{
    public function __construct(private readonly ProductRepository $vendorProductRepository) {}

    public function execute(int $limit, int $page): GetProductListResponseDTO
    {
        $requestDTO = new GetProductListRequestDTO($limit, $page);
        $responseDTO = $this->vendorProductRepository->getProductList($requestDTO);

        if ($responseDTO instanceof ErrorResponseDTO) {
            throw new CatalogFetchException('No hem pogut carregar el catàleg: ' . $responseDTO->message);
        }

        return $responseDTO;
    }
}
