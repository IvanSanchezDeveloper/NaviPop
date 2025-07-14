<?php

namespace App\Service;

use App\Entity\User;
use App\Handler\ImageHandler;
use App\Repository\ProductRepository;

class ProductManager
{
    private ProductRepository $productRepository;
    private string $imagesPath;

    public function __construct(ProductRepository $productRepository, string $imagesPath)
    {
        $this->productRepository = $productRepository;
        $this->imagesPath = $imagesPath;
    }

    public function createProduct(User $user, string $name, string $price, mixed $image)
    {
        ImageHandler::validateImage($image);

        $fileName = ImageHandler::getImageFileName($image);

        $this->productRepository->createProduct($user, $name, $price, $fileName);

        ImageHandler::saveImage($image, $this->imagesPath);
    }
}