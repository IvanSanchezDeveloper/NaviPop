<?php

namespace App\Tests\IntegrationTests\Service\Auth;

use App\Entity\User;
use App\Exception\GoogleLoginRequiredException;
use App\Exception\LinkGoogleAccountException;
use App\Exception\UserNotFoundException;
use App\Exception\WrongCredentialsException;
use App\Repository\UserRepository;
use App\Service\Auth\AuthManager;
use App\Tests\IntegrationTests\TestCase\AbstractIntegrationTestCase;

class AuthManagerIntegrationTest extends AbstractIntegrationTestCase
{
    private AuthManager $authManager;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authManager = new AuthManager($this->entityManager, $this->passwordHasher);
        $this->userRepository = $this->entityManager->getRepository(User::class);
    }

    public function testHandleGoogleLoginCreatesNewUserIsPersistedToDb(): void
    {
        $newGoogleId = 'new_google_123';
        $newEmail = 'newgoogle@test.com';

        $createdUser = $this->authManager->handleGoogleLogin($newEmail, $newGoogleId);

        $this->assertEquals($newEmail, $createdUser->getEmail());
        $this->assertEquals($newGoogleId, $createdUser->getGoogleId());
        $this->assertEquals('', $createdUser->getPassword());

        $userFromDb = $this->userRepository->findOneByEmail($newEmail);
        $this->assertNotNull($userFromDb);
        $this->assertEquals($newGoogleId, $userFromDb->getGoogleId());
    }
}