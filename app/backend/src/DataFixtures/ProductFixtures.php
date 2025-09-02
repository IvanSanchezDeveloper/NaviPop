<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{

    private const FIXTURES_IMAGES_PATH = 'fixtures/';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $images = [
            'BALON.jpeg', 'CAMISETA.jpeg', 'CARGADOR.jpeg', 'LIBRO.jpeg',
            'PS5.jpeg', 'SWITCH.jpeg', 'TAZA.jpeg', 'TECLADO.jpeg',
        ];

        for ($i = 1; $i <= 50; $i++) {
            $product = new Product();

            $product->setName($faker->text(30));
            $product->setImagePath(self::FIXTURES_IMAGES_PATH . $faker->randomElement($images));
            $product->setPrice($faker->randomFloat(2, 5, 500));
            $product->setCreatedAt($faker->dateTime());

            $randomUserId = $faker->numberBetween(1, UserFixtures::MAX_USER_FIXTURES);
            $product->setUserSeller($this->getReference("user_$randomUserId", User::class));

            $manager->persist($product);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
