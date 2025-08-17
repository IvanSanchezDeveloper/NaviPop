<?php

namespace App\Tests\IntegrationTests\Controller\Api;

use App\Entity\User;
use App\Tests\IntegrationTests\TestCase\AbstractApiIntegrationTestCase;
use Symfony\Component\HttpFoundation\Response;

class AuthControllerIntegrationTest extends AbstractApiIntegrationTestCase
{
    public function testRegisterCreatesUserAndPersistsInDb(): void
    {
        $requestData = [
            'email' => self::TEST_EMAIL,
            'password' =>  self::TEST_PASSWORD
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($requestData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneByEmail(self::TEST_EMAIL);
        $this->assertNotNull($user);
    }
}