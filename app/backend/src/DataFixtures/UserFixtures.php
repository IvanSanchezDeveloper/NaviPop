<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const MAX_USER_FIXTURES = 10;

    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 1; $i <= self::MAX_USER_FIXTURES; $i++) {
            $user = new User();
            $user->setEmail($faker->unique()->safeEmail());
            $user->setRoles([User::ROLE_USER]);
            $user->setPassword($this->hasher->hashPassword($user, $faker->password()));
            $user->setCreatedAt($faker->dateTime());

            $manager->persist($user);

            $this->addReference("user_$i", $user);
        }

        $manager->flush();
    }
}
