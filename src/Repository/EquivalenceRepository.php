<?php
// src/Repository/EquivalenceRepository.php

namespace App\Repository;

use App\Entity\Diplome;
use App\Entity\Equivalence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class EquivalenceRepository extends ServiceEntityRepository
{
    public const ALLOWED_STATUSES = [
        'draft', 'submitted', 'in_review', 'in_committee', 'approved', 'rejected'
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Equivalence::class);
    }

    /**
     * Derniers dossiers décidés (commission)
     */
    public function findLastDecided(int $limit = 10): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.status IN (:statuses)')
            ->setParameter('statuses', ['approved', 'rejected'])
            ->orderBy('e.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Tous les dossiers avec leur diplôme (pour index)
     */
    public function findAllWithDiplome(): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.diplomeReference', 'd')
            ->addSelect('d')
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Dossier avec toutes ses relations (pour édition et affichage détaillé)
     */
    public function findWithRelations(int $id): ?Equivalence
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.diplomeReference', 'd')
            ->addSelect('d')
            ->leftJoin('d.etablissement', 'et')
            ->addSelect('et')
            ->leftJoin('et.pays', 'p')
            ->addSelect('p')
            ->leftJoin('e.nationalite', 'n')   // relation ManyToOne vers Pays
            ->addSelect('n')
            ->where('e.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Statistiques par statut pour un diplôme donné
     */
    public function countByDiplomeAndStatus(Diplome $diplome): array
    {
        $results = $this->createQueryBuilder('e')
            ->select('e.status AS status')
            ->addSelect('COUNT(e.id) AS total')
            ->where('e.diplomeReference = :diplome')
            ->setParameter('diplome', $diplome)
            ->groupBy('e.status')
            ->getQuery()
            ->getArrayResult();

        $stats = array_fill_keys(self::ALLOWED_STATUSES, 0);
        foreach ($results as $row) {
            $status = $row['status'];
            if (array_key_exists($status, $stats)) {
                $stats[$status] = (int) $row['total'];
            }
        }
        return $stats;
    }

    /**
     * QueryBuilder filtré pour un diplôme
     */
    public function createFilteredByDiplomeQueryBuilder(
        Diplome $diplome,
        ?string $status = null,
        ?string $search = null
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('e')
            ->where('e.diplomeReference = :diplome')
            ->setParameter('diplome', $diplome)
            ->orderBy('e.createdAt', 'DESC');

        if ($status !== null && in_array($status, self::ALLOWED_STATUSES, true)) {
            $qb->andWhere('e.status = :status')->setParameter('status', $status);
        }
        if ($search !== null && trim($search) !== '') {
            $search = mb_substr(trim($search), 0, 100);
            $qb->andWhere('LOWER(e.nom) LIKE LOWER(:search) OR LOWER(e.prenom) LIKE LOWER(:search) OR LOWER(e.numeroDossier) LIKE LOWER(:search)')
               ->setParameter('search', '%' . $search . '%');
        }
        return $qb;
    }

    /**
     * Pagination pour la liste des dossiers liés à un diplôme (utilisé par le contrôleur admin)
     */
    public function paginateForDiplome(
        int $diplomeId,
        PaginatorInterface $paginator,
        int $page,
        array $filters = []
    ): PaginationInterface {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.diplomeReference', 'd')
            ->addSelect('d')
            ->where('d.id = :diplomeId')
            ->setParameter('diplomeId', $diplomeId)
            ->orderBy('e.createdAt', 'DESC');

        if (!empty($filters['status'])) {
            $qb->andWhere('e.status = :status')->setParameter('status', $filters['status']);
        }
        if (!empty($filters['nom'])) {
            $qb->andWhere('e.nom LIKE :nom OR e.prenom LIKE :nom')
               ->setParameter('nom', '%' . $filters['nom'] . '%');
        }
        if (!empty($filters['numero'])) {
            $qb->andWhere('e.numeroDossier LIKE :numero')
               ->setParameter('numero', '%' . $filters['numero'] . '%');
        }

        return $paginator->paginate($qb, $page, 20);
    }
    // src/Repository/EquivalenceRepository.php

    public function getNextNumeroDossierForCurrentYearOld(): string
    {
        $year = (int) date('Y');
        $pattern = '%/' . $year;

        // Récupérer le dernier numéro de l'année courante
        $last = $this->createQueryBuilder('e')
            ->select('e.numeroDossier')
            ->where('e.numeroDossier LIKE :pattern')
            ->setParameter('pattern', $pattern)
            ->orderBy('e.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$last) {
            $nextNumber = 1;
        } else {
            // Extraire la partie avant le slash
            $parts = explode('/', $last->getNumeroDossier());
            $currentNumber = (int) $parts[0];
            $nextNumber = $currentNumber + 1;
        }

        return sprintf('%d/%d', $nextNumber, $year);
    }
    /**
 * Retourne le prochain numéro de dossier séquentiel pour l'année en cours.
 * Format : <numéro séquentiel>/<année> (ex: 23647/2023)
 */
public function getNextNumeroDossierForCurrentYear(): string
{
    $year = (int) date('Y');
    $pattern = '%/' . $year;

    $last = $this->createQueryBuilder('e')
        ->select('e.numeroDossier')
        ->where('e.numeroDossier LIKE :pattern')
        ->setParameter('pattern', $pattern)
        ->orderBy('e.id', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();

    if (!$last) {
        $nextNumber = 1;
    } else {
        $parts = explode('/', $last->getNumeroDossier());
        $currentNumber = (int) $parts[0];
        $nextNumber = $currentNumber + 1;
    }

    return sprintf('%d/%d', $nextNumber, $year);
}
}
