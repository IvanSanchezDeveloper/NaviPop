<?php declare(strict_types=1);

namespace App\Service\Auth;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AuthResponse
{
    private const JWT_COOKIE_NAME = 'BEARER';
    private const HTTP_OK = 200;

    public static function createJWTCookie(string $token): Cookie
    {
        return Cookie::create(
            self::JWT_COOKIE_NAME,
            $token,
            new \DateTime('+1 month')
        )
        ->withSecure(true)
        ->withHttpOnly(true)
        ->withSameSite('None');
    }
    public static function json(array $data, ?string $token = null, int $status = self::HTTP_OK): JsonResponse
    {
        $response = new JsonResponse($data, $status);

        if ($token) {
            $response->headers->setCookie(self::createJWTCookie($token));
        }

        return $response;
    }

    public static function logout(int $status = self::HTTP_OK): JsonResponse
    {
        $response = new JsonResponse([], $status);

        $response->headers->clearCookie(self::JWT_COOKIE_NAME);

        return $response;
    }

    public static function redirect(string $url, ?string $token = null): RedirectResponse
    {
        $response = new RedirectResponse($url);

        if ($token) {
            $response->headers->setCookie(self::createJWTCookie($token));
        }

        return $response;
    }
}