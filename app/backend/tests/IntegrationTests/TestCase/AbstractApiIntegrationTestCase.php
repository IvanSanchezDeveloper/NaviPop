<?php declare(strict_types=1);

namespace App\Tests\IntegrationTests\TestCase;

use App\Entity\User;
use App\Tests\IntegrationTests\TestCase\AbstractIntegrationTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use function Symfony\Component\String\u;

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

    protected function loginUser(): User
    {
        $user = $this->createTestUser();

        $jwtManager = static::getContainer()->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user);
        $this->client->setServerParameter('HTTP_Authorization', 'Bearer ' . $token);

        return $user;
    }
}