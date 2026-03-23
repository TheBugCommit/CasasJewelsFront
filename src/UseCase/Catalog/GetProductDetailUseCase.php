<?php

namespace App\UseCase\Catalog;

use Sce\CasasJewelsVendor\DTO\Entity\ProductDTO;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductDetailRequestDTO;
use Sce\CasasJewelsVendor\DTO\ErrorResponseDTO;
use Sce\CasasJewelsVendor\Repository\ProductRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetProductDetailUseCase
{
    public function __construct(private readonly ProductRepository $vendorProductRepository) {}

    /**
     * @throws NotFoundHttpException Si el producte no existeix
     */
    public function execute(string $id): ProductDTO
    {
        $requestDTO = new GetProductDetailRequestDTO($id);
        $responseDTO = $this->vendorProductRepository->getProductDetail($requestDTO);

        if ($responseDTO instanceof ErrorResponseDTO) {
            throw new NotFoundHttpException('El producte que busques no existeix o ha estat eliminat.');
        }

        return $responseDTO->data;
    }
}
