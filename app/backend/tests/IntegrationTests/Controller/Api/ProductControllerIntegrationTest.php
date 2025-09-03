<?php

namespace App\Tests\IntegrationTests\Controller\Api;

use App\Entity\Product;
use App\Entity\User;
use App\Tests\IntegrationTests\TestCase\AbstractApiIntegrationTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class ProductControllerIntegrationTest extends AbstractApiIntegrationTestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->loginUser();
    }

    public function testProductIsCreatedAndPersistedInDb(): void
    {
        $this->client->request(
            'POST',
            '/api/product/new',
            [
                'name' => self::TEST_NAME,
                'price' => self::TEST_PRICE,
            ],
            [
                'image' => $this->createUploadedFile(),
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $productRepository = $this->entityManager->getRepository(Product::class);
        $products = $productRepository->findAll();
        $this->assertEquals($products[0]->getName(), self::TEST_NAME);

        //Remove generated image
        $fileName = $products[0]->getFilename();
        $uploadDir = $this->client->getKernel()->getProjectDir() . '/public/uploads/products/';
        $filePath = $uploadDir . $fileName;

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        self::assertFalse(file_exists($filePath));
    }

    public function testPaginationWorks(): void
    {
        $product1 = $this->createTestProduct($this->user);
        $product2 = $this->createTestProduct($this->user);

        $this->client->request('GET', '/api/products', [
            'page' => 1,
            'limit' => 1
        ]);

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);

        $this->assertEquals($product1->getId(), $data['data'][0]['id']);
        $this->assertEquals(1, count($data['data']));
        $this->assertEquals(1, $data['pagination']['current_page']);
        $this->assertEquals(1, $data['pagination']['per_page']);
        $this->assertEquals(2, $data['pagination']['total_items']);
        $this->assertEquals(2, $data['pagination']['total_pages']);
    }
}