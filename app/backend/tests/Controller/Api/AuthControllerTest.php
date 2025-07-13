<?php declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Exception\LinkGoogleAccountException;
use App\Exception\WrongCredentialsException;
use App\Service\Auth\AuthManager;
use App\Controller\Api\AuthController;
use App\Tests\TestCase\AbstractApiTestCase;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use League\OAuth2\Client\Provider\GoogleUser;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthControllerTest extends AbstractApiTestCase
{
    protected const FRONTEND_URL = 'http://frontend.test';
    protected const LOGIN_ENDPOINT = '/api/login';
    protected const GOOGLE_REDIRECT_URL = 'https://accounts.google.com/o/oauth2/auth';
    protected const GOOGLE_LOGIN_SUCCESS_REDIRECT_URL = self::FRONTEND_URL;
    protected const GOOGLE_LOGIN_ERROR_REDIRECT_URL = self::FRONTEND_URL . '/login?error=' . 'Account+exists.+Please+link+your+Google+account.';

    private AuthManager|MockObject $loginManagerMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginManagerMock = $this->createMock(AuthManager::class);
    }

    public function testLoginSuccess(): void
    {
        $this->loginManagerMock->method('handleStandardLogin')
            ->willReturn($this->user);

        $controller = new AuthController('', $this->loginManagerMock);

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
        $this->loginManagerMock->method('handleStandardLogin')
            ->willThrowException(new WrongCredentialsException());

        $controller = new AuthController('', $this->loginManagerMock);

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
            $this->loginManagerMock
        );

        $response = $controller->connectGoogle($clientRegistryMock);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(self::GOOGLE_REDIRECT_URL, $response->getTargetUrl());
    }

    public function testConnectGoogleCheckSuccess(): void
    {
        $this->loginManagerMock->method('handleGoogleLogin')
            ->willReturn($this->user);

        $controller = new AuthController(
            self::FRONTEND_URL,
            $this->loginManagerMock
        );

        $response = $controller->connectGoogleCheck(
            $this->mockClientRegistry($this->mockGoogleUser()),
            $this->mockJwtManager()
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(self::GOOGLE_LOGIN_SUCCESS_REDIRECT_URL, $response->getTargetUrl());

        $cookies = $response->headers->getCookies();
        $this->assertCount(1, $cookies);
        $this->assertEquals('BEARER', $cookies[0]->getName());
        $this->assertEquals(self::JWT_TOKEN, $cookies[0]->getValue());
    }

    public function testConnectGoogleCheckFails(): void
    {
        $this->loginManagerMock->method('handleGoogleLogin')
            ->willThrowException(new LinkGoogleAccountException(self::EMAIL));

        $controller = new AuthController(
            self::FRONTEND_URL,
            $this->loginManagerMock
        );

        $response = $controller->connectGoogleCheck(
            $this->mockClientRegistry($this->mockGoogleUser()),
            $this->mockJwtManager()
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(self::GOOGLE_LOGIN_ERROR_REDIRECT_URL, $response->getTargetUrl());
        $this->assertEmpty($response->headers->getCookies());
    }

    public function testLogout(): void
    {
        $controller = new AuthController('', $this->loginManagerMock);
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
}