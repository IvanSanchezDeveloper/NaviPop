<?php declare(strict_types=1);

namespace App\Tests\IntegrationTests\TestCase;

use App\Tests\IntegrationTests\TestCase\AbstractIntegrationTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class AbstractApiIntegrationTestCase extends AbstractIntegrationTestCase
{
    protected KernelBrowser $client;
    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $this->entityManager->beginTransaction();
    }

    protected function loginUser(): void
    {
        $jwtManager = static::getContainer()->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($this->createTestUser());
        $this->client->setServerParameter('HTTP_Authorization', 'Bearer ' . $token);
    }
}