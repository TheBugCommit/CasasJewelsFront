<?php

namespace App\UseCase\Catalog;

use App\Exception\ProductNotFoundException;
use Sce\CasasJewelsVendor\DTO\Entity\ProductDTO;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductDetailRequestDTO;
use Sce\CasasJewelsVendor\DTO\ErrorResponseDTO;
use Sce\CasasJewelsVendor\Repository\ProductRepository;

class GetProductDetailUseCase
{
    public function __construct(private readonly ProductRepository $vendorProductRepository) {}

    public function execute(string $id): ProductDTO
    {
        $requestDTO = new GetProductDetailRequestDTO($id);
        $responseDTO = $this->vendorProductRepository->getProductDetail($requestDTO);

        if ($responseDTO instanceof ErrorResponseDTO) {
            throw new ProductNotFoundException('El producte que busques no existeix o ha estat eliminat.');
        }

        return $responseDTO->data;
    }
}
