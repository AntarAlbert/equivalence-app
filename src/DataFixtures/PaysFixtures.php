<?php

namespace App\DataFixtures;

use App\Entity\Pays;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PaysFixtures extends Fixture
{
    public const PAYS_REFERENCE = 'pays_';

    public function load(ObjectManager $manager): void
    {
        $paysList = [
            [1, 'MG', 'MDG', 'Madagascar', 'Madagascar'],
            [2, 'FR', 'FRA', 'France', 'France'],
            [3, 'US', 'USA', 'United States', 'États-Unis'],
        ];

        foreach ($paysList as [$code, $alpha2, $alpha3, $en, $fr]) {
            $pays = new Pays();
            $pays->setCode($code)
                ->setAlpha2($alpha2)
                ->setAlpha3($alpha3)
                ->setNomEnGb($en)
                ->setNomFrFr($fr);

            $manager->persist($pays);
            $this->addReference(self::PAYS_REFERENCE . $alpha2, $pays);
        }

        $manager->flush();
    }
}
