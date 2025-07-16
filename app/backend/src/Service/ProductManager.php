<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use App\Entity\User;
use App\Handler\ImageHandler;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductManager
{
    private ProductRepository $productRepository;
    private string $imagesPath;

    public function __construct(ProductRepository $productRepository, string $imagesPath)
    {
        $this->productRepository = $productRepository;
        $this->imagesPath = $imagesPath;
    }


    public function createProduct(User $user, string $name, string $price, UploadedFile $image)
    {
        ImageHandler::validateImage($image);

        $fileName = ImageHandler::getUniqueFileName($image);

        $this->productRepository->createProduct($user, $name, $price, $fileName);

        ImageHandler::saveImage($image, $this->imagesPath, $fileName);
    }

    /**
     * @return Product[]
     */
    public function getAllProducts(): array
    {
        return $this->productRepository->findAll();
    }

    public function getProductById(int $id): ?Product
    {
        return $this->productRepository->find($id);
    }

    public function formatProductData(Product $product, string $baseUrl): array
    {
        return [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'price' => $product->getPrice(),
            'image' => $baseUrl . '/uploads/products/' . $product->getImagePath(),
        ];
    }
}