<?php
// src/Repository/ArreteRepository.php

namespace App\Repository;

use App\Entity\Arrete;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Arrete>
 */
class ArreteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Arrete::class);
    }

    /**
     * Récupère tous les arrêtés triés par date décroissante.
     *
     * @return Arrete[]
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.dateArrete', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve un arrêté par son numéro.
     */
    public function findByNumero(string $numero): ?Arrete
    {
        return $this->createQueryBuilder('a')
            ->where('a.numeroArrete = :numero')
            ->setParameter('numero', $numero)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Récupère les arrêtés liés à un dossier d'équivalence.
     *
     * @return Arrete[]
     */
    public function findByEquivalence(int $equivalenceId): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.equivalence = :equivalenceId')
            ->setParameter('equivalenceId', $equivalenceId)
            ->orderBy('a.dateArrete', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre d'arrêtés pour une année donnée (utile pour générer un numéro séquentiel).
     */
    public function countForYear(int $year): int
    {
        $start = new \DateTimeImmutable("$year-01-01");
        $end = new \DateTimeImmutable(($year + 1) . "-01-01");

        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.dateArrete >= :start')
            ->andWhere('a.dateArrete < :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Génère le prochain numéro d'arrêté séquentiel pour l'année courante.
     * Format : NNN/YYYY (ex: 001/2025)
     */
    public function getNextNumero(): string
    {
        $year = (int) date('Y');
        $count = $this->countForYear($year);
        $nextNumber = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        return sprintf('%s/%d', $nextNumber, $year);
    }
}
