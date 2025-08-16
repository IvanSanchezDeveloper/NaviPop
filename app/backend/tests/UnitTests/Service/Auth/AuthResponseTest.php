<?php declare(strict_types=1);

namespace App\Tests\UnitTests\Service\Auth;

use App\Service\Auth\AuthResponse;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AuthResponseTest extends TestCase
{
    private const TEST_TOKEN = 'test_token';
    private const TEST_URL = 'http://test.com';

    public function testCreateJWTCookie(): void
    {
        $cookie = AuthResponse::createJWTCookie(self::TEST_TOKEN);

        $this->assertInstanceOf(Cookie::class, $cookie);
        $this->assertEquals('BEARER', $cookie->getName());
        $this->assertEquals(self::TEST_TOKEN, $cookie->getValue());
    }

    public function testJsonResponseWithToken(): void
    {
        $data = ['test' => 'data'];
        $response = AuthResponse::json($data, self::TEST_TOKEN);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($data, json_decode($response->getContent(), true));
        $this->assertCount(1, $response->headers->getCookies());

        $cookie = $response->headers->getCookies()[0];
        $this->assertEquals('BEARER', $cookie->getName());
        $this->assertEquals(self::TEST_TOKEN, $cookie->getValue());
    }

    public function testJsonResponseWithoutToken(): void
    {
        $data = ['test' => 'data'];
        $response = AuthResponse::json($data);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($data, json_decode($response->getContent(), true));
        $this->assertCount(0, $response->headers->getCookies());
    }

    public function testRedirectResponseWithToken(): void
    {
        $response = AuthResponse::redirect(self::TEST_URL, self::TEST_TOKEN);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(self::TEST_URL, $response->getTargetUrl());
        $this->assertCount(1, $response->headers->getCookies());

        $cookie = $response->headers->getCookies()[0];
        $this->assertEquals('BEARER', $cookie->getName());
        $this->assertEquals(self::TEST_TOKEN, $cookie->getValue());
    }

    public function testRedirectResponseWithoutToken(): void
    {
        $response = AuthResponse::redirect(self::TEST_URL);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(self::TEST_URL, $response->getTargetUrl());
        $this->assertCount(0, $response->headers->getCookies());
    }

    public function testLogout(): void
    {
        $response = AuthResponse::logout();

        $this->assertInstanceOf(JsonResponse::class, $response);

        $this->assertCount(1, $response->headers->getCookies());

        $cookie = $response->headers->getCookies()[0];

        $this->assertEquals('BEARER', $cookie->getName());
        $this->assertEquals('', $cookie->getValue());
        $this->assertTrue($cookie->isCleared());
    }
}