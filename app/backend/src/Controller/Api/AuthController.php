<?php declare(strict_types=1);

namespace App\Controller\Api;

use App\Exception\AbstractApiException;
use App\Exception\UserAlreadyInUseException;
use App\Repository\UserRepository;
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
    private AuthManager $authManager;

    public function __construct(string $frontendDomain, AuthManager $loginManager)
    {
        $this->frontendDomain = $frontendDomain;
        $this->authManager = $loginManager;
    }

    #[Route('/api/login', name: 'login', methods: ['POST'])]
    public function login(
        Request $request,
        JWTTokenManagerInterface $JWTManager,
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            $user = $this->authManager->handleStandardLogin(
                $data['email'] ?? '',
                $data['password'] ?? ''
            );

            $token = $JWTManager->create($user);

            return AuthResponse::json([], $token);

        } catch (AbstractApiException $e) {
            return AuthResponse::json([
                'error' => $e->getMessage()
            ], null, $e->getStatusCode());
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
            $user = $this->authManager->handleGoogleLogin(
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

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserRepository $userRepo,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'];
        $password = $data['password'];

        if ($userRepo->findOneBy(['email' => $email])) {
            $exception = new UserAlreadyInUseException();
            return AuthResponse::json([
                'error' => $exception->getMessage()
            ], null, $exception->getStatusCode());
        }

        $user = $this->authManager->register($email, $password);

        $token = $jwtManager->create($user);

        return AuthResponse::json([], $token);
    }
}
