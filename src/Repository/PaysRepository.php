<?php
// src/Repository/PaysRepository.php

namespace App\Repository;

use App\Entity\Pays;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pays>
 */
class PaysRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pays::class);
    }

    /**
     * Trouve un pays par son code alpha-2 (ex: FR)
     */
    public function findOneByAlpha2(string $alpha2): ?Pays
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.alpha2 = :alpha2')
            ->setParameter('alpha2', strtoupper($alpha2))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve un pays par son code alpha-3 (ex: FRA)
     */
    public function findOneByAlpha3(string $alpha3): ?Pays
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.alpha3 = :alpha3')
            ->setParameter('alpha3', strtoupper($alpha3))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve un pays par son code numérique (ex: 250)
     */
    public function findOneByCode(int $code): ?Pays
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Récupère tous les pays triés par nom français
     */
    public function findAllOrderedByNomFr(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.nomFrFr', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère tous les pays triés par code alpha-2
     */
    public function findAllOrderedByAlpha2(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.alpha2', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche les pays dont le nom français contient une chaîne
     */
    public function searchByNomFr(string $searchTerm): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.nomFrFr LIKE :term')
            ->setParameter('term', '%' . $searchTerm . '%')
            ->orderBy('p.nomFrFr', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
