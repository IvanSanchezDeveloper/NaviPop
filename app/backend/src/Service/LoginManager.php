<?php
namespace App\Service;

use App\Entity\User;
use App\Exception\GoogleLoginRequiredException;
use App\Exception\LinkGoogleAccountException;
use App\Exception\UserNotFoundException;
use App\Exception\WrongCredentialsException;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginManager
{
    private EntityManagerInterface $em;
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        $this->em = $em;
        $this->userRepository = $em->getRepository(User::class);
        $this->passwordHasher = $passwordHasher;
    }

    public function handleStandardLogin(string $email, string $password): User
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            throw new UserNotFoundException();
        }

        if ($user->isGoogleUser()) {
            throw new GoogleLoginRequiredException($email);
        }

        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            throw new WrongCredentialsException('Wrong credentials');
        }

        return $user;
    }

    public function handleGoogleLogin(string $email, ?string $googleId = null): User
    {
        $googleUser = $this->userRepository->findOneBy(['googleId' => $googleId]);

        if ($googleUser) {
            return $googleUser;
        }


        $existingUser = $this->userRepository->findOneBy(['email' => $email]);
        if ($existingUser) {
            throw new LinkGoogleAccountException($email, $googleId);
        }

        $newUser = new User();
        $newUser->setEmail($email);
        $newUser->setGoogleId($googleId);

        $this->em->persist($newUser);
        $this->em->flush();

        return $newUser;
    }
}
