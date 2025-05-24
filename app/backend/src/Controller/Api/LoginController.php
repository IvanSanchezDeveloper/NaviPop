<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
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
        EntityManager $em,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $client = $clientRegistry->getClient('google');
        $googleUser = $client->fetchUser();

        $email = $googleUser->getEmail();

        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setRoles(['ROLE_USER']);
            $em->persist($user);
            $em->flush();
        }

        $token = $jwtManager->create($user);

        return new JsonResponse([
            'token' => $token,
            'user' => [
                'email' => $user->getEmail(),
                'roles' => $user->getRoles()
            ]
        ]);
    }
}
