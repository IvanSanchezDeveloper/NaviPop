<?php

namespace App\Tests\Controller\Api;

use App\Controller\Api\ProductController;
use App\Entity\Product;
use App\Entity\User;
use App\Exception\ImageMaxSizeExceededException;
use App\Service\ProductManager;
use Couchbase\RegexpSearchQuery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ProductControllerTest extends TestCase
{
    private ProductManager $productManager;
    private ProductController $controller;
    private User $user;

    protected function setUp(): void
    {
        $this->productManager = $this->createMock(ProductManager::class);


        $this->user = new User();

        $this->controller = $this->getMockBuilder(ProductController::class)
            ->setConstructorArgs([$this->productManager])
            ->onlyMethods(['getUser'])
            ->getMock();

        $this->controller->expects($this->any())
            ->method('getUser')
            ->willReturn($this->user);
    }

    public function testCreateProductSuccess(): void
    {
        $request = new Request();
        $request->request->set('name', 'Test Product');
        $request->request->set('price', '19.99');

        $uploadedFile = $this->createMock(UploadedFile::class);
        $request->files->set('image', $uploadedFile);

        $this->productManager->expects($this->once())
            ->method('createProduct')
            ->with($this->user, 'Test Product', '19.99', $uploadedFile);

        $response = $this->controller->create($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }


    public function testCreateProductHandlesImageException(): void
    {
        $request = new Request();
        $request->request->set('name', 'Test Product');
        $request->request->set('price', '19.99');

        $uploadedFile = $this->createMock(UploadedFile::class);
        $request->files->set('image', $uploadedFile);

        $exception = new ImageMaxSizeExceededException();

        $this->productManager->expects($this->once())
            ->method('createProduct')
            ->willThrowException($exception);

        $response = $this->controller->create($request);

        $this->assertEquals($exception->getStatusCode(), $response->getStatusCode());
    }

    public function testGetProductsSuccess(): void
    {
        $products = [new Product(), new Product()];

        $this->productManager->expects($this->once())
            ->method('getAllProducts')
            ->willReturn($products);

        $this->productManager->expects($this->exactly(2))
            ->method('formatProductData')
            ->willReturn(['id' => 1, 'name' => 'Test']);

        $response = $this->controller->getProducts(new Request());

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);

        $this->assertTrue($data['success']);
        $this->assertCount(2, $data['data']);
    }

    public function testGetProductByIdSuccess(): void
    {
        $product = new Product();
        $productId = 1;

        $this->productManager->expects($this->once())
            ->method('getProductById')
            ->with($productId)
            ->willReturn($product);

        $this->productManager->expects($this->once())
            ->method('formatProductData')
            ->willReturn(['id' => 1, 'name' => 'Test']);

        $response = $this->controller->getProduct(new Request(), $productId);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals(1, $data['data']['id']);
        $this->assertEquals('Test', $data['data']['name']);
    }

    public function testGetProductByIdNotFound(): void
    {
        $productId = 999;

        $this->productManager->expects($this->once())
            ->method('getProductById')
            ->with($productId)
            ->willReturn(null);

        $response = $this->controller->getProduct(new Request(), $productId);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Product not found', $data['error']);
    }
}