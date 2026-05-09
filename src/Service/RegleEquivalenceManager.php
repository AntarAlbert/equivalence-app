<?php
namespace App\Service;

use App\Entity\RegleEquivalence;
use App\Repository\RegleEquivalenceRepository;
use Doctrine\ORM\EntityManagerInterface;

class RegleEquivalenceManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private RegleEquivalenceRepository $repository
    ) {}

    public function save(RegleEquivalence $regle): void
    {
        // Désactiver les anciennes règles actives du même diplôme
        $anciennes = $this->repository->findBy([
            'diplome' => $regle->getDiplome(),
            'actif' => true
        ]);
        foreach ($anciennes as $ancienne) {
            if ($ancienne !== $regle) {
                $ancienne->setActif(false);
            }
        }
        // La nouvelle règle devient active
        $regle->setActif(true);

        $this->em->persist($regle);
        $this->em->flush();
    }
}
