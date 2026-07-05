<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public const USER_REFERENCE = 'user_';

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $etabs = $manager->getRepository(\App\Entity\Etablissement::class)->findAll();

        $roles = ['ROLE_CANDIDAT', 'ROLE_AGENT', 'ROLE_COMMISSION', 'ROLE_ADMIN'];

        for ($i = 0; $i < 15; $i++) {
            $user = new User();
            $user->setEmail($faker->unique()->safeEmail())
                ->setNom($faker->lastName())
                ->setPrenom($faker->firstName())
                ->setRoles([$faker->randomElement($roles)])
                ->setEtablissement($faker->randomElement($etabs));

            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
            $user->setPassword($hashedPassword);

            $manager->persist($user);
            $this->addReference(self::USER_REFERENCE . $i, $user);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [EtablissementFixtures::class];
    }
}
