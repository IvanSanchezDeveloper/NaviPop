<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
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

    public function __construct(string $frontendDomain, string $frontendLoginCallbackEndpoint)
    {
        $this->frontendDomain = $frontendDomain;
        $this->frontendLoginCallbackEndpoint = $frontendLoginCallbackEndpoint;
    }

    #[Route('/api/login', name: 'login', methods: ['POST'])]
    public function login(
        Request $request,
        UserRepository $userRepository,
        JWTTokenManagerInterface $JWTManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['error' => 'Credenciales inválidas'], 401);
        }

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

        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword('');
            $em->persist($user);
            $em->flush();
        }

        $token = $jwtManager->create($user);

        $redirectUrl = $this->frontendDomain . $this->frontendLoginCallbackEndpoint . '?token=' . $token;

        return $this->redirect($redirectUrl);
    }
}
