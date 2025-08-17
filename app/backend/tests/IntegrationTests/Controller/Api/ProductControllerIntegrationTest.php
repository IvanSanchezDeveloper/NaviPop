<?php

namespace App\Tests\IntegrationTests\Controller\Api;

use App\Entity\Product;
use App\Tests\IntegrationTests\TestCase\AbstractApiIntegrationTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class ProductControllerIntegrationTest extends AbstractApiIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loginUser();
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
        $fileName = $products[0]->getImagePath();
        $uploadDir = $this->client->getKernel()->getProjectDir() . '/public/uploads/products/';
        $filePath = $uploadDir . $fileName;

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        self::assertFalse(file_exists($filePath));
    }
}