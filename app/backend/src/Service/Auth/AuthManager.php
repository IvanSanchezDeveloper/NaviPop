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
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

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

        $newUser = $this->register($email, null, $googleId);

        return $newUser;
    }

    public static function buildAuthHtml(
        string $title,
        string $message,
        array $postMessage,
        string $fallback,
        string $targetDomain = "*",
    ): string {

        $payload = json_encode($postMessage);

        return <<<HTML
            <html>
                <head>
                  <title>{$title}</title>
                  <script>
                    window.onload = function () {
                        window.opener?.postMessage($payload, "$targetDomain");
                        try { window.close(); } catch (e) {
                          document.body.innerHTML = "<p>{$fallback}</p>";
                        }
                    };
                  </script>
                </head>
                <body>
                  <p>{$message}</p>
                </body>
            </html>
        HTML;
    }

    public function generateGoogleAuthCode(CacheInterface $cache, string $token): string
    {
        $authCode = hash('sha256', random_bytes(32));

        $cache->get($authCode, function (ItemInterface $item) use ($token) {
            $item->expiresAfter(60); // 1 minute expiration
            return $token;
        });

        return $authCode;
    }

    public function register(string $email, ?string $password, ?string $googleId = null): User
    {
        return $this->userRepository->createUser($email, $password, $googleId);
    }
}
