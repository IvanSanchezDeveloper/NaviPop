<?php

namespace App\Tests\Controller\Api;

use App\Controller\Api\LoginController;
use App\Exception\LinkGoogleAccountException;
use App\Service\LoginManager;
use App\Tests\TestCase\AbstractApiTestCase;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class LoginControllerTest extends AbstractApiTestCase
{
    protected const FRONTEND_URL = 'http://frontend.test';
    protected const FRONTEND_CALLBACK = '/callback';
    protected const LOGIN_ENDPOINT = '/api/login';
    protected const GOOGLE_REDIRECT_URL = 'https://accounts.google.com/o/oauth2/auth';
    protected const GOOGLE_LOGIN_SUCCESS_REDIRECT_URL = self::FRONTEND_URL . self::FRONTEND_CALLBACK . '?token=' . self::JWT_TOKEN;
    protected const GOOGLE_LOGIN_ERROR_REDIRECT_URL = self::FRONTEND_URL . self::FRONTEND_CALLBACK . '?error=' . 'Account+exists.+Please+link+your+Google+account.';

    private LoginManager|MockObject $loginManagerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginManagerMock = $this->createMock(LoginManager::class);
    }

    public function testLoginSuccess(): void
    {
        $this->loginManagerMock->method('handleStandardLogin')->willReturn($this->user);

        $controller = new LoginController('', '', $this->loginManagerMock);

        $request = new Request([], [], [], [], [], [], json_encode([
            'email' => self::EMAIL,
            'password' => self::PASSWORD,
        ]));

        $response = $controller->login($request, $this->mockJwtManager());

        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(self::JWT_TOKEN, $data['token']);
        $this->assertEquals(self::EMAIL, $data['user']['email']);
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
            ->method('getClient')->with('google')->willReturn($oauthClientMock);

        $controller = new LoginController(self::FRONTEND_URL, self::FRONTEND_CALLBACK, $this->loginManagerMock);
        $response = $controller->connectGoogle($clientRegistryMock);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(self::GOOGLE_REDIRECT_URL, $response->getTargetUrl());
    }

    public function testConnectGoogleCheckSuccess(): void
    {
        $controller = new LoginController(self::FRONTEND_URL, self::FRONTEND_CALLBACK, $this->loginManagerMock);

        $response = $controller->connectGoogleCheck(
            $this->mockClientRegistry($this->mockGoogleUser()),
            $this->mockJwtManager()
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(self::GOOGLE_LOGIN_SUCCESS_REDIRECT_URL, $response->getTargetUrl());
    }

    public function testConnectGoogleCheckFails(): void
    {
        $this->loginManagerMock->method('handleGoogleLogin')
            ->willThrowException(new LinkGoogleAccountException(self::EMAIL));

        $controller = new LoginController(self::FRONTEND_URL, self::FRONTEND_CALLBACK, $this->loginManagerMock);

        $response = $controller->connectGoogleCheck(
            $this->mockClientRegistry($this->mockGoogleUser()),
            $this->mockJwtManager()
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(self::GOOGLE_LOGIN_ERROR_REDIRECT_URL, $response->getTargetUrl());
    }
}
