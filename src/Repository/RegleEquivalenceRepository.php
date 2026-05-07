<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Diplome;
use App\Entity\RegleEquivalence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RegleEquivalence>
 */
class RegleEquivalenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RegleEquivalence::class);
    }

   public function findActiveRule(Diplome $diplome, \DateTimeInterface $date = null): ?RegleEquivalence
{
    $date = $date ?? new \DateTimeImmutable();
    return $this->createQueryBuilder('r')
        ->where('r.diplome = :diplome')
        ->andWhere('r.actif = true')
        ->andWhere('r.deletedAt IS NULL')
        ->andWhere('r.dateDebut <= :date')
        ->andWhere('r.dateFin IS NULL OR r.dateFin >= :date')
        ->setParameter('diplome', $diplome)
        ->setParameter('date', $date)
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
}

    /**
     * Retourne toutes les règles d’un diplôme
     * triées de la plus récente à la plus ancienne.
     */
    public function findByDiplomeOrderedByDate(
        Diplome $diplome
    ): array {
        return $this->createQueryBuilder('r')
            ->where('r.diplome = :diplome')
            ->setParameter('diplome', $diplome)
            ->orderBy('r.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne la règle la plus récente.
     */
    public function findMostRecentRuleForDiplome(
        Diplome $diplome
    ): ?RegleEquivalence {
        return $this->createQueryBuilder('r')
            ->where('r.diplome = :diplome')
            ->setParameter('diplome', $diplome)
            ->orderBy('r.dateDebut', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Retourne toutes les règles valides actuellement.
     */
    public function findAllValidNow(): array
    {
        $now = new \DateTimeImmutable();

        return $this->createQueryBuilder('r')
            ->where('r.actif = true')
            ->andWhere('r.dateDebut <= :now')
            ->andWhere('r.dateFin IS NULL OR r.dateFin >= :now')
            ->setParameter('now', $now)
            ->orderBy('r.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifie si une période chevauche
     * une règle existante.
     */
    public function hasOverlap(
        Diplome $diplome,
        \DateTimeInterface $dateDebut,
        ?\DateTimeInterface $dateFin = null,
        ?int $excludeId = null
    ): bool {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.diplome = :diplome')
            ->setParameter('diplome', $diplome);

        /**
         * Cas :
         * règle avec date de fin.
         */
        if ($dateFin !== null) {

            $qb
                ->andWhere('r.dateDebut <= :dateFin')
                ->andWhere(
                    'r.dateFin IS NULL OR r.dateFin >= :dateDebut'
                )
                ->setParameter('dateFin', $dateFin);

        } else {

            /**
             * Cas :
             * règle sans date de fin.
             */
            $qb
                ->andWhere(
                    'r.dateFin IS NULL OR r.dateFin >= :dateDebut'
                );
        }

        $qb->setParameter('dateDebut', $dateDebut);

        /**
         * Exclusion lors de modification.
         */
        if ($excludeId !== null) {

            $qb
                ->andWhere('r.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        return (int) $qb
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }
   public function findActiveRuleForDiploma(Diplome $diplome, ?\DateTimeInterface $date = null): ?RegleEquivalence
{
    $date = $date ?? new \DateTimeImmutable();
    return $this->createQueryBuilder('r')
        ->andWhere('r.diplome = :diplome')
        ->andWhere('r.actif = true')   // ← changé active → actif
        ->andWhere('r.dateDebut <= :date')
        ->andWhere('r.dateFin IS NULL OR r.dateFin >= :date')
        ->setParameter('diplome', $diplome)
        ->setParameter('date', $date)
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
}
}
