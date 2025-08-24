<?php declare(strict_types=1);

namespace App\Tests\UnitTests\Controller\Api;

use App\Controller\Api\AuthController;
use App\Entity\User;
use App\Exception\LinkGoogleAccountException;
use App\Exception\WrongCredentialsException;
use App\Repository\UserRepository;
use App\Service\Auth\AuthManager;
use App\Tests\UnitTests\TestCase\AbstractApiTestCase;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use League\OAuth2\Client\Provider\GoogleUser;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthControllerTest extends AbstractApiTestCase
{
    protected const FRONTEND_URL = 'http://frontend.test';
    protected const LOGIN_ENDPOINT = '/api/login';
    protected const GOOGLE_REDIRECT_URL = 'https://accounts.google.com/o/oauth2/auth';
    protected const ONE_TIME_CODE = 'test1234';

    private AuthManager|MockObject $authManagerMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authManagerMock = $this->createMock(AuthManager::class);
    }

    public function testLoginSuccess(): void
    {
        $this->authManagerMock->method('handleStandardLogin')
            ->willReturn($this->user);

        $controller = new AuthController('', $this->authManagerMock);

        $request = new Request([], [], [], [], [], [], json_encode([
            'email' => self::EMAIL,
            'password' => self::PASSWORD,
        ]));

        $response = $controller->login($request, $this->mockJwtManager());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $cookies = $response->headers->getCookies();
        $this->assertCount(1, $cookies);
        $this->assertEquals('BEARER', $cookies[0]->getName());
        $this->assertEquals(self::JWT_TOKEN, $cookies[0]->getValue());
    }

    public function testLoginFailure(): void
    {
        $this->authManagerMock->method('handleStandardLogin')
            ->willThrowException(new WrongCredentialsException());

        $controller = new AuthController('', $this->authManagerMock);

        $request = new Request([], [], [], [], [], [], json_encode([
            'email' => self::EMAIL,
            'password' => 'wrong_password',
        ]));

        $response = $controller->login($request, $this->mockJwtManager());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertEmpty($response->headers->getCookies());
    }

    public function testConnectGoogleRedirectsToOAuth(): void
    {
        $oauthClientMock = $this->createMock(OAuth2Client::class);
        $oauthClientMock->expects($this->once())
            ->method('redirect')
            ->with(['email', 'profile'])
            ->willReturn(new RedirectResponse(self::GOOGLE_REDIRECT_URL));

        $clientRegistryMock = $this->createMock(ClientRegistry::class);
        $clientRegistryMock->expects($this->once())
            ->method('getClient')
            ->with('google')
            ->willReturn($oauthClientMock);

        $controller = new AuthController(
            self::FRONTEND_URL,
            $this->authManagerMock
        );

        $request = new Request();

        $response = $controller->connectGoogle($clientRegistryMock, $request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(self::GOOGLE_REDIRECT_URL, $response->getTargetUrl());
    }

    public function testConnectGoogleCheckSuccess(): void
    {
        $this->authManagerMock->method('handleGoogleLogin')
            ->willReturn($this->user);

        $controller = new AuthController(
            self::FRONTEND_URL,
            $this->authManagerMock
        );

        $response = $controller->connectGoogleCheck(
            $this->mockClientRegistry($this->mockGoogleUser()),
            $this->mockJwtManager(),
            $this->mockCache()
        );

        $this->assertInstanceOf(Response::class, $response);
        $content = $response->getContent();

        $this->assertStringContainsString('Login Successful', $content);
        $this->assertStringContainsString('oneTimeCode', $content);
    }

    public function testConnectGoogleCheckFails(): void
    {
        $exception = new LinkGoogleAccountException('');
        $this->authManagerMock->method('handleGoogleLogin')
            ->willThrowException($exception);

        $controller = new AuthController(
            self::FRONTEND_URL,
            $this->authManagerMock
        );

        $response = $controller->connectGoogleCheck(
            $this->mockClientRegistry($this->mockGoogleUser()),
            $this->mockJwtManager(),
            $this->mockCache()
        );

        $this->assertInstanceOf(Response::class, $response);
        $content = $response->getContent();

        $this->assertStringContainsString('Login Error', $content);
        $this->assertStringContainsString($exception->getMessage(), $content);
    }


    public function testSetGoogleLoginCookieWithNoCode(): void
    {
        $controller = new AuthController(
            self::FRONTEND_URL,
            $this->authManagerMock
        );

        $request = new Request([], [], [], [], [], [], json_encode([]));
        $response = $controller->setGoogleLoginCookie($request, $this->mockCache());

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertStringContainsString('Unexpected error', $response->getContent());
    }

    public function testSetGoogleLoginCookieWithExpiredCode(): void
    {
        $controller = new AuthController(
            self::FRONTEND_URL,
            $this->authManagerMock
        );

        $request = new Request([], [], [], [], [], [], json_encode(['oneTimeCode' => 'expired']));
        $response = $controller->setGoogleLoginCookie($request, $this->mockCache());

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertStringContainsString('Session expired', $response->getContent());
    }

    public function testSetGoogleLoginCookieSucceeds(): void
    {
        $controller = new AuthController(
            self::FRONTEND_URL,
            $this->authManagerMock
        );

        $cache = $this->mockCache(self::JWT_TOKEN, self::ONE_TIME_CODE);

        $request = new Request([], [], [], [], [], [], json_encode(['oneTimeCode' => self::ONE_TIME_CODE]));
        $response = $controller->setGoogleLoginCookie($request, $cache);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $cookies = $response->headers->getCookies();
        $this->assertCount(1, $cookies);
        $this->assertEquals('BEARER', $cookies[0]->getName());
        $this->assertEquals(self::JWT_TOKEN, $cookies[0]->getValue());
    }

    public function testLogout(): void
    {
        $controller = new AuthController('', $this->authManagerMock);
        $response = $controller->logout();

        $this->assertInstanceOf(JsonResponse::class, $response);

        $cookies = $response->headers->getCookies();
        $this->assertCount(1, $cookies);
        $this->assertEquals('BEARER', $cookies[0]->getName());
        $this->assertEquals('', $cookies[0]->getValue());
        $this->assertTrue($cookies[0]->isCleared());
    }

    protected function mockGoogleUser(): GoogleUser
    {
        $googleUserMock = $this->createMock(GoogleUser::class);
        $googleUserMock->method('getEmail')->willReturn(self::EMAIL);
        $googleUserMock->method('getId')->willReturn(self::GOOGLE_ID);
        return $googleUserMock;
    }

    protected function mockClientRegistry(GoogleUser $googleUser): ClientRegistry
    {
        $googleClientMock = $this->createMock(OAuth2Client::class);
        $googleClientMock->method('fetchUser')->willReturn($googleUser);

        $clientRegistryMock = $this->createMock(ClientRegistry::class);
        $clientRegistryMock->method('getClient')
            ->with('google')
            ->willReturn($googleClientMock);

        return $clientRegistryMock;
    }

    public function testRegisterReturnsSuccessResponse(): void
    {
        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->with(['email' => self::EMAIL])->willReturn(null);

        $userRepo->method('createUser')
            ->with(self::EMAIL, self::PASSWORD)
            ->willReturn($this->user);

        $jwtManager = $this->mockJwtManager();

        $request = new Request(content: json_encode([
            'email' => self::EMAIL,
            'password' => self::PASSWORD,
            'name' => self::NAME,
        ]));

        $controller = new AuthController('', $this->authManagerMock);

        $response = $controller->register(
            $request,
            $userRepo,
            $jwtManager
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $cookies = $response->headers->getCookies();
        $this->assertCount(1, $cookies);
        $this->assertEquals('BEARER', $cookies[0]->getName());
        $this->assertEquals(self::JWT_TOKEN, $cookies[0]->getValue());
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
            'name' => self::NAME,
        ]));

        $controller = new AuthController('', $this->authManagerMock);

        $response = $controller->register(
            $request,
            $userRepo,
            $this->mockJwtManager()
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_CONFLICT, $response->getStatusCode());
        $this->assertEmpty($response->headers->getCookies());
    }
}