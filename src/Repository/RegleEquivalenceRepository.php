<?php
// src/Repository/RegleEquivalenceRepository.php

namespace App\Repository;

use App\Entity\Diplome;
use App\Entity\RegleEquivalence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RegleEquivalenceRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry
    ) {
        parent::__construct(
            $registry,
            RegleEquivalence::class
        );
    }

    /**
     * Retourne uniquement les règles non archivées
     */
    public function findVisible(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.deletedAt IS NULL')
            ->orderBy('r.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifie chevauchement période
     */
    public function hasOverlap(
        ?Diplome $diplome,
        ?\DateTimeInterface $dateDebut,
        ?\DateTimeInterface $dateFin,
        ?int $excludeId = null
    ): bool {

        if (!$diplome || !$dateDebut) {
            return false;
        }

        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.diplome = :diplome')
            ->andWhere('r.deletedAt IS NULL')
            ->setParameter('diplome', $diplome);

        if ($excludeId !== null) {
            $qb->andWhere('r.id != :excludeId')
               ->setParameter('excludeId', $excludeId);
        }

        $qb->andWhere(
            '(r.dateFin IS NULL OR r.dateFin >= :dateDebut)'
        )
        ->setParameter('dateDebut', $dateDebut);

        if ($dateFin !== null) {

            $qb->andWhere(
                '(r.dateDebut <= :dateFin)'
            )
            ->setParameter('dateFin', $dateFin);
        }

        return (int) $qb
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    /**
     * Retourne règle active
     */
    public function findActiveRule(
        ?Diplome $diplome,
        ?\DateTimeInterface $date
    ): ?RegleEquivalence {

        if (!$diplome || !$date) {
            return null;
        }

        return $this->createQueryBuilder('r')
            ->andWhere('r.diplome = :diplome')
            ->andWhere('r.actif = true')
            ->andWhere('r.deletedAt IS NULL')
            ->andWhere('r.dateDebut <= :date')
            ->andWhere(
                '(r.dateFin IS NULL OR r.dateFin >= :date)'
            )
            ->setParameter('diplome', $diplome)
            ->setParameter('date', $date)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
    // src/Repository/RegleEquivalenceRepository.php

// ... à l'intérieur de la classe

/**
 * Retourne toutes les règles actives (non archivées) pour un diplôme donné
 *
 * @return RegleEquivalence[]
 */
public function findActiveRulesForDiplome(Diplome $diplome): array
{
    return $this->createQueryBuilder('r')
        ->andWhere('r.diplome = :diplome')
        ->andWhere('r.actif = true')
        ->andWhere('r.deletedAt IS NULL')
        ->setParameter('diplome', $diplome)
        ->orderBy('r.dateDebut', 'DESC')
        ->getQuery()
        ->getResult();
}
}
