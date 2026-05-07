<?php

namespace App\Repository;

use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Document>
 */
class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    /**
     * @return Document[]
     */
    public function findByEquivalence(int $equivalenceId): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.equivalence = :eqId')
            ->setParameter('eqId', $equivalenceId)
            ->orderBy('d.uploadedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Document[]
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.type = :type')
            ->setParameter('type', $type)
            ->orderBy('d.uploadedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
