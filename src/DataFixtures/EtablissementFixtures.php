<?php

namespace App\DataFixtures;

use App\Entity\Etablissement;
use App\Entity\Pays;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class EtablissementFixtures extends Fixture implements DependentFixtureInterface
{
    public const ETABLISSEMENT_REFERENCE = 'etablissement_';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $paysRepo = $manager->getRepository(Pays::class);
        $allPays = $paysRepo->findAll();

        for ($i = 0; $i < 10; $i++) {
            $etab = new Etablissement();
            $etab->setNom($faker->company())
                ->setVille($faker->city())
                ->setType($faker->randomElement(['Université', 'Institut', 'École']))
                ->setPays($faker->randomElement($allPays));

            $manager->persist($etab);
            $this->addReference(self::ETABLISSEMENT_REFERENCE . $i, $etab);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [PaysFixtures::class];
    }
}
