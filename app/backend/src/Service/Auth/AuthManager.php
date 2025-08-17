<?php
namespace App\Service\Auth;

use App\Entity\User;
use App\Exception\GoogleLoginRequiredException;
use App\Exception\LinkGoogleAccountException;
use App\Exception\UserNotFoundException;
use App\Exception\WrongCredentialsException;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthManager
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
        $user = $this->userRepository->findOneByEmail($email);

        if (!$user) {
            throw new UserNotFoundException();
        }

        if ($user->isGoogleUser()) {
            throw new GoogleLoginRequiredException();
        }

        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            throw new WrongCredentialsException();
        }

        return $user;
    }

    public function handleGoogleLogin(string $email, ?string $googleId = null): User
    {
        $googleUser = $this->userRepository->findOneByGoogleId($googleId);

        if ($googleUser) {
            return $googleUser;
        }


        $existingUser = $this->userRepository->findOneByEmail($email);
        if ($existingUser) {
            throw new LinkGoogleAccountException($email);
        }

        $newUser = new User();
        $newUser->setEmail($email);
        $newUser->setGoogleId($googleId);
        $newUser->setPassword(''); // No need password for Google accounts

        $this->em->persist($newUser);
        $this->em->flush();

        return $newUser;
    }
}
