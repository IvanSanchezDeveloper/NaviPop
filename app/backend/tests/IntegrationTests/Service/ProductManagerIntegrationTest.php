<?php

namespace App\Tests\IntegrationTests\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\ProductManager;
use App\Tests\IntegrationTests\TestCase\AbstractIntegrationTestCase;

class ProductManagerIntegrationTest extends AbstractIntegrationTestCase
{
    private ProductManager $productManager;
    private ProductRepository $productRepository;
    private string $imagesPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->entityManager->getRepository(Product::class);
        $this->imagesPath = sys_get_temp_dir() . '/test/images';
        $this->productManager = new ProductManager($this->productRepository, $this->imagesPath);
    }

    public function testCreateProductPersistsProductInDbAndSavesImage(): void
    {
        $user = $this->createTestUser();
        $productName = 'Integration Test Product';
        $productPrice = '25.99';
        $uploadedFile = $this->createUploadedFile();

        $this->productManager->createProduct($user, $productName, $productPrice, $uploadedFile);

        $products = $this->productRepository->findAll();
        $this->assertCount(1, $products);

        $product = $products[0];
        $this->assertEquals($productName, $product->getName());
        $this->assertEquals($productPrice, $product->getPrice());
        $this->assertEquals($user->getId(), $product->getUserSeller()->getId());
        $this->assertNotEmpty($product->getImagePath());

        $imagePath = $this->imagesPath . '/' . $product->getImagePath();
        $this->assertFileExists($imagePath);
    }

    public function testGetAllProductsReturnsAllProducts(): void
    {
        $user1 = $this->createTestUser('user1@test.com');
        $user2 = $this->createTestUser('user2@test.com');

        $this->productRepository->createProduct($user1, 'Product 1', '10.00', 'image1.jpg');
        $this->productRepository->createProduct($user2, 'Product 2', '20.00', 'image2.jpg');

        $allProducts = $this->productManager->getAllProducts();

        $this->assertCount(2, $allProducts);
        $this->assertEquals('Product 1', $allProducts[0]->getName());
        $this->assertEquals('Product 2', $allProducts[1]->getName());
    }
}