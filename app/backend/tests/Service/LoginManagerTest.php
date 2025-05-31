<?php
namespace App\Tests\Service;

use App\Entity\User;
use App\Exception\GoogleLoginRequiredException;
use App\Exception\LinkGoogleAccountException;
use App\Exception\UserNotFoundException;
use App\Exception\WrongCredentialsException;
use App\Repository\UserRepository;
use App\Service\LoginManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginManagerTest extends TestCase
{
    private const EMAIL = 'email@example.com';
    private const PASSWORD = 'password';
    private const GOOGLE_ID = 'google-123';

    private EntityManagerInterface $em;
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private LoginManager $loginManager;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $this->em->method('getRepository')->willReturn($this->userRepository);

        $this->loginManager = new LoginManager($this->em, $this->passwordHasher);
    }

    private function createUserMock(bool $isGoogleUser = false): User
    {
        $user = $this->createMock(User::class);
        $user->method('isGoogleUser')->willReturn($isGoogleUser);
        $user->method('getEmail')->willReturn(self::EMAIL);
        $user->method('getGoogleId')->willReturn(self::GOOGLE_ID);
        return $user;
    }

    public function testHandleStandardLoginThrowsUserNotFoundException(): void
    {
        $this->userRepository->method('findOneByEmail')->willReturn(null);
        $this->expectException(UserNotFoundException::class);

        $this->loginManager->handleStandardLogin(self::EMAIL, self::PASSWORD);
    }

    public function testHandleStandardLoginThrowsGoogleRequiredLoginException(): void
    {
        $user = $this->createUserMock(true);
        $this->userRepository->method('findOneByEmail')->willReturn($user);
        $this->expectException(GoogleLoginRequiredException::class);

        $this->loginManager->handleStandardLogin(self::EMAIL, self::PASSWORD);
    }

    public function testHandleStandardLoginThrowsWrongCredentialsException(): void
    {
        $user = $this->createUserMock(false);
        $this->userRepository->method('findOneByEmail')->willReturn($user);
        $this->passwordHasher->method('isPasswordValid')->willReturn(false);

        $this->expectException(WrongCredentialsException::class);

        $this->loginManager->handleStandardLogin(self::EMAIL, self::PASSWORD);
    }

    public function testHandleStandardLoginSuccess(): void
    {
        $user = $this->createUserMock(false);
        $this->userRepository->method('findOneByEmail')->willReturn($user);
        $this->passwordHasher->method('isPasswordValid')->willReturn(true);

        $loggedUser = $this->loginManager->handleStandardLogin(self::EMAIL, self::PASSWORD);

        $this->assertSame($user, $loggedUser);
    }

    public function testHandleGoogleLoginThrowsLinkGoogleAccountException(): void
    {
        $user = $this->createUserMock(false);
        $this->userRepository->method('findOneByGoogleId')->willReturn(null);
        $this->userRepository->method('findOneByEmail')->willReturn($user);

        $this->expectException(LinkGoogleAccountException::class);

        $this->loginManager->handleGoogleLogin(self::EMAIL, self::GOOGLE_ID);
    }

    public function testHandleGoogleLoginSuccessWhenUserFound(): void
    {
        $user = $this->createUserMock();
        $this->userRepository->method('findOneByGoogleId')->willReturn($user);

        $loggedUser = $this->loginManager->handleGoogleLogin(self::EMAIL, self::GOOGLE_ID);

        $this->assertSame($user, $loggedUser);
    }

    public function testHandleGoogleLoginCreatesNewUser(): void
    {
        $this->userRepository->method('findOneByGoogleId')->willReturn(null);
        $this->userRepository->method('findOneByEmail')->willReturn(null);

        $newUser = $this->loginManager->handleGoogleLogin(self::EMAIL, self::GOOGLE_ID);

        $this->assertNotNull($newUser);
        $this->assertSame(self::EMAIL, $newUser->getEmail());
        $this->assertSame(self::GOOGLE_ID, $newUser->getGoogleId());
    }
}
