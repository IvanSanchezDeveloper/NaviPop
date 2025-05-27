<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\LoginManager;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class LoginController extends AbstractController
{
    private string $frontendDomain;
    private string $frontendLoginCallbackEndpoint;
    private LoginManager $loginManager;

    public function __construct(string $frontendDomain, string $frontendLoginCallbackEndpoint, LoginManager $loginManager)
    {
        $this->frontendDomain = $frontendDomain;
        $this->frontendLoginCallbackEndpoint = $frontendLoginCallbackEndpoint;
        $this->loginManager = $loginManager;
    }

    #[Route('/api/login', name: 'login', methods: ['POST'])]
    public function login(
        Request $request,
        UserRepository $userRepository,
        JWTTokenManagerInterface $JWTManager,
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $user = $this->loginManager->handleStandardLogin($email, $password);

        $token = $JWTManager->create($user);

        return new JsonResponse([
            'token' => $token,
            'user' => [
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ]
        ]);
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
        EntityManagerInterface $em,
        JWTTokenManagerInterface $jwtManager
    ): RedirectResponse {
        $client = $clientRegistry->getClient('google');
        $googleUser = $client->fetchUser();

        $email = $googleUser->getEmail();
        $googleId = $googleUser->getId();

        $user = $this->loginManager->handleGoogleLogin($email, $googleId);

        $token = $jwtManager->create($user);

        $redirectUrl = $this->frontendDomain . $this->frontendLoginCallbackEndpoint . '?token=' . $token;

        return $this->redirect($redirectUrl);
    }

}
