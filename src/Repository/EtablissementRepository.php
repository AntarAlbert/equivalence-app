<?php
// src/Repository/EtablissementRepository.php

namespace App\Repository;

use App\Entity\Etablissement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Etablissement>
 */
class EtablissementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Etablissement::class);
    }

    /**
     * Récupère tous les établissements triés par nom, avec leur pays.
     *
     * @return Etablissement[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.pays', 'p')
            ->addSelect('p')
            ->orderBy('e.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche les établissements par nom (partiel, insensible à la casse).
     *
     * @param string $searchTerm
     * @return Etablissement[]
     */
    public function searchByName(string $searchTerm): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.pays', 'p')
            ->addSelect('p')
            ->where('LOWER(e.nom) LIKE LOWER(:term)')
            ->setParameter('term', '%' . $searchTerm . '%')
            ->orderBy('e.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les établissements d’un pays donné.
     *
     * @param int $paysId
     * @return Etablissement[]
     */
    public function findByPays(int $paysId): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.pays', 'p')
            ->addSelect('p')
            ->where('p.id = :paysId')
            ->setParameter('paysId', $paysId)
            ->orderBy('e.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les établissements qui ont au moins un diplôme associé.
     *
     * @return Etablissement[]
     */
    public function findWithDiplomes(): array
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.diplomes', 'd')
            ->addSelect('d')
            ->orderBy('e.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Nettoie les clés étrangères invalides (remplace les ID 0 par NULL).
     * Cette méthode est utile à exécuter après une migration ou en script.
     */
    public function fixInvalidForeignKeys(): void
    {
        $conn = $this->getEntityManager()->getConnection();
        $conn->executeStatement('UPDATE etablissement SET pays_id = NULL WHERE pays_id = 0');
        $conn->executeStatement('UPDATE etablissement SET pays_id = NULL WHERE pays_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM pays WHERE id = etablissement.pays_id)');
    }
}
