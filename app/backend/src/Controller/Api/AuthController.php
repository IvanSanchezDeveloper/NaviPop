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
use Symfony\Contracts\Cache\CacheInterface;

final class AuthController extends AbstractController
{
    private string $frontendDomain;
    private AuthManager $authManager;

    public function __construct(string $frontendDomain, AuthManager $authManager)
    {
        $this->frontendDomain = $frontendDomain;
        $this->authManager = $authManager;
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
        $client = $clientRegistry->getClient('google');

        return $client->redirect(
            ['email', 'profile'],
        );
    }

    #[Route('/api/login/google/check', name: 'connect_google_validation')]
    public function connectGoogleCheck(
        ClientRegistry $clientRegistry,
        JWTTokenManagerInterface $jwtManager,
        CacheInterface $cache,
    ): Response {
        try {
            $googleUser = $clientRegistry->getClient('google')->fetchUser();
            $user = $this->authManager->handleGoogleLogin(
                $googleUser->getEmail(),
                $googleUser->getId()
            );

            $token = $jwtManager->create($user);

            $authCode = $this->authManager->generateGoogleAuthCode($cache, $token);

            $html = AuthManager::buildAuthHtml(
                'Login Successful',
                'Finishing login...',
                ['success' => true, 'oneTimeCode' => $authCode],
                'Login successful. You can close this window.',
                $this->frontendDomain
            );

            return AuthResponse::html($html);

        } catch (AbstractApiException $e) {
            $error = $e->getMessage();

            $html = AuthManager::buildAuthHtml(
                'Login Error',
                'Error during login...',
                ['error' => $error ],
                "Login failed: {$e->getMessage()}",
                $this->frontendDomain
            );

            return AuthResponse::html($html);
        }
    }

    #[Route('/api/login/google/cookie', methods: ['POST'], name: 'connect_google_cookie')]
    public function setGoogleLoginCookie(Request $request, CacheInterface $cache): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $code = $data['oneTimeCode'] ?? null;

        if (!$code) {
            return AuthResponse::json(['error' => 'Unexpected error'], null, RESPONSE::HTTP_BAD_REQUEST);
        }

        $token = $cache->getItem($code)->get();

        if (!$token) {
            return AuthResponse::json(['error' => 'Session expired'], null, RESPONSE::HTTP_BAD_REQUEST);
        }

        $cache->deleteItem($code);

        return AuthResponse::json([], $token);
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
