<?php

namespace App\Tests\Controller\Api;

use App\Controller\Api\RegisterController;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\TestCase\AbstractApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegisterControllerTest extends AbstractApiTestCase
{

    public function testRegisterReturnsSuccessResponse(): void
    {
        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->with(['email' => self::EMAIL])->willReturn(null);

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')->willReturn('');

        $jwtManager = $this->mockJwtManager();

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $request = new Request(content: json_encode([
            'email' => self::EMAIL,
            'password' => self::PASSWORD,
        ]));

        $controller = new RegisterController();

        $response = $controller->register(
            $request,
            $em,
            $userRepo,
            $passwordHasher,
            $jwtManager
        );

        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(self::JWT_TOKEN, $data['token']);
        $this->assertEquals(self::EMAIL, $data['user']['email']);
        $this->assertContains(self::USER_ROLE, $data['user']['roles']);
    }

    public function testRegisterReturnsErrorForDuplicateEmail(): void
    {
        $existingUser = new User();
        $existingUser->setEmail(self::EMAIL);

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->willReturn($existingUser);

        $request = new Request(content: json_encode([
            'email' => self::EMAIL,
            'password' => self::PASSWORD,
        ]));

        $controller = new RegisterController();

        $response = $controller->register(
            $request,
            $this->createMock(EntityManagerInterface::class),
            $userRepo,
            $this->createMock(UserPasswordHasherInterface::class),
            $this->mockJwtManager()
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Email already in use', $data['message']);
    }
}
