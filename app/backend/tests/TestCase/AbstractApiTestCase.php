<?php

namespace App\Tests\TestCase;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractApiTestCase extends WebTestCase
{
    protected const GOOGLE_ID = 'google-123';
    protected const EMAIL = 'email@example.com';
    protected const PASSWORD = 'password';
    protected const JWT_TOKEN = 'google.jwt.token';
    protected const USER_ROLE = 'ROLE_USER';

    protected KernelBrowser $client;
    protected User $user;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->user = $this->createTestUser();
    }

    protected function createTestUser(): User
    {
        $user = new User();
        $user->setEmail(self::EMAIL);
        $user->setPassword(self::PASSWORD);
        $user->setRoles([self::USER_ROLE]);
        $user->setGoogleId(self::GOOGLE_ID);
        return $user;
    }

    protected function mockJwtManager(string $token = self::JWT_TOKEN): JWTTokenManagerInterface
    {
        $jwtManagerMock = $this->createMock(JWTTokenManagerInterface::class);
        $jwtManagerMock->method('create')->willReturn($token);
        return $jwtManagerMock;
    }
}
