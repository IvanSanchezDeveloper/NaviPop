<?php

namespace App\Tests\Service;

use App\Entity\Product;
use App\Entity\User;
use App\Repository\ProductRepository;
use App\Service\ProductManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductManagerTest extends TestCase
{
    private ProductRepository $productRepository;
    private ProductManager $productManager;
    private string $imagesPath;

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->imagesPath = sys_get_temp_dir() . '/test/images';
        $this->productManager = new ProductManager($this->productRepository, $this->imagesPath);
    }

    public function testCreateProductSuccess(): void
    {
        $user = new User();
        $name = 'Test Product';
        $price = '19.99';

        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('getMimeType')->willReturn('image/jpeg');
        $uploadedFile->method('getSize')->willReturn(1024 * 1024); // 1MB
        $uploadedFile->method('guessExtension')->willReturn('jpg');
        $uploadedFile->method('move');

        $this->productRepository->expects($this->once())
            ->method('createProduct')
            ->with($user, $name, $price, $this->isType('string'));

        $this->productManager->createProduct($user, $name, $price, $uploadedFile);
    }

    public function testGetAllProductsReturnsArray(): void
    {
        $products = [new Product(), new Product()];

        $this->productRepository->expects($this->once())
            ->method('findAll')
            ->willReturn($products);

        $result = $this->productManager->getAllProducts();

        $this->assertSame($products, $result);
    }

    public function testGetProductByIdReturnsProduct(): void
    {
        $product = new Product();
        $productId = 1;

        $this->productRepository->expects($this->once())
            ->method('find')
            ->with($productId)
            ->willReturn($product);

        $result = $this->productManager->getProductById($productId);

        $this->assertSame($product, $result);
    }

    public function testGetProductByIdReturnsNullWhenNotFound(): void
    {
        $productId = 999;

        $this->productRepository->expects($this->once())
            ->method('find')
            ->with($productId)
            ->willReturn(null);

        $result = $this->productManager->getProductById($productId);

        $this->assertNull($result);
    }

    public function testFormatProductDataReturnsCorrectArray(): void
    {
        $product = new Product();
        $product->setName('Test Product');
        $product->setPrice('19.99');
        $product->setImagePath('test.jpg');

        $baseUrl = 'https://example.com';

        $result = $this->productManager->formatProductData($product, $baseUrl);

        $expected = [
            'id' => null,
            'name' => 'Test Product',
            'price' => '19.99',
            'image' => 'https://example.com/uploads/products/test.jpg'
        ];

        $this->assertEquals($expected, $result);
    }
}