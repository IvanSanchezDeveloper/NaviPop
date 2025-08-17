<?php declare(strict_types=1);

namespace App\Tests\IntegrationTests\TestCase;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class AbstractIntegrationTestCase extends WebTestCase
{
    protected const TEST_EMAIL = 'integration@test.com';
    protected const TEST_PASSWORD = 'integration_password';
    protected const TEST_GOOGLE_ID = 'google_integration_123';
    protected const TEST_NAME = 'test_name';
    protected const TEST_PRICE = '123';

    protected EntityManagerInterface $entityManager;
    protected UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->rollback();
        }

        $this->entityManager->close();
        parent::tearDown();
    }

    protected function createTestUser(string $email = self::TEST_EMAIL, ?string $googleId = null): User
    {
        $user = new User();
        $user->setEmail($email);

        if ($googleId) {
            $user->setGoogleId($googleId);
            $user->setPassword('');
        } else {
            $hashedPassword = $this->passwordHasher->hashPassword($user, self::TEST_PASSWORD);
            $user->setPassword($hashedPassword);
        }

        $user->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    protected function createUploadedFile(string $filename = 'test.jpg'): UploadedFile
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'fake_image_');

        $image = imagecreatetruecolor(1, 1);
        imagejpeg($image, $tmpPath, 80);
        imagedestroy($image);

        return new UploadedFile(
            $tmpPath,
            $filename,
            'image/jpeg',
            null,
            true
        );
    }
}