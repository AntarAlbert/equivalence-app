<?php
// src/Repository/ConsiderantRepository.php

namespace App\Repository;

use App\Entity\Considerant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Considerant>
 */
class ConsiderantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Considerant::class);
    }

    /**
     * Récupère tous les considérants d’un arrêté, triés par ordre croissant.
     *
     * @return Considerant[]
     */
    public function findByArreteOrdered(int $arreteId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.arrete = :arreteId')
            ->setParameter('arreteId', $arreteId)
            ->orderBy('c.ordre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les considérants par type (loi, décret, etc.).
     *
     * @return Considerant[]
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.type = :type')
            ->setParameter('type', $type)
            ->orderBy('c.date', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
