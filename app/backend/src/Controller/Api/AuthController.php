<?php declare(strict_types=1);

namespace App\Controller\Api;

use App\Exception\AbstractApiException;
use App\Service\Auth\AuthManager;
use App\Service\Auth\AuthResponse;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AuthController extends AbstractController
{
    private string $frontendDomain;
    private AuthManager $loginManager;

    public function __construct(string $frontendDomain, AuthManager $loginManager)
    {
        $this->frontendDomain = $frontendDomain;
        $this->loginManager = $loginManager;
    }

    #[Route('/api/login', name: 'login', methods: ['POST'])]
    public function login(
        Request $request,
        JWTTokenManagerInterface $JWTManager,
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            $user = $this->loginManager->handleStandardLogin(
                $data['email'] ?? '',
                $data['password'] ?? ''
            );

            $token = $JWTManager->create($user);

            return AuthResponse::json([], $token);

        } catch (AbstractApiException $e) {
            return AuthResponse::json([
                'error' => $e->getMessage()
            ], null, Response::HTTP_UNAUTHORIZED);
        }
    }


    #[Route('/api/login/google', name: 'connect_google')]
    public function connectGoogle(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry->getClient('google')->redirect([
            'email', 'profile'
        ]);
    }

    #[Route('/api/login/google/check', name: 'connect_google_validation')]
    public function connectGoogleCheck(
        ClientRegistry $clientRegistry,
        JWTTokenManagerInterface $jwtManager
    ): RedirectResponse {
        try {
            $googleUser = $clientRegistry->getClient('google')->fetchUser();
            $user = $this->loginManager->handleGoogleLogin(
                $googleUser->getEmail(),
                $googleUser->getId()
            );

            $token = $jwtManager->create($user);

            return AuthResponse::redirect(
                $this->frontendDomain,
                $token
            );

        } catch (AbstractApiException $e) {
            return $this->redirect(
                $this->frontendDomain . '/login'
                . '?error=' . urlencode($e->getMessage() ?: 'Login failed')
            );
        }
    }

    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        return AuthResponse::logout();
    }

}
