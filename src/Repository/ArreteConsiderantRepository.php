<?php
// src/Repository/ArreteConsiderantRepository.php

namespace App\Repository;

use App\Entity\ArreteConsiderant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArreteConsiderant>
 */
class ArreteConsiderantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArreteConsiderant::class);
    }

    /**
     * @return ArreteConsiderant[]
     */
    public function findByArreteOrdered(int $arreteId): array
    {
        return $this->createQueryBuilder('ac')
            ->where('ac.arrete = :arreteId')
            ->setParameter('arreteId', $arreteId)
            ->orderBy('ac.ordre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
