<?php

namespace App\Repository;

use App\Entity\Diplome;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Diplome>
 */
class DiplomeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Diplome::class);
    }

    /**
     * Recherche les diplômes par pays.
     * @return Diplome[]
     */
    public function findByPays(string $pays): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.pays = :pays')
            ->setParameter('pays', strtoupper(trim($pays)))
            ->orderBy('d.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche par cadre et échelle.
     * @return Diplome[]
     */
    public function findByCadreEtEchelle(string $cadre, string $echelle): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.cadre = :cadre')
            ->andWhere('d.echelle = :echelle')
            ->setParameter('cadre', $cadre)
            ->setParameter('echelle', $echelle)
            ->orderBy('d.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère tous les diplômes triés par pays puis titre.
     * @return Diplome[]
     */
   // src/Repository/DiplomeRepository.php

   // src/Repository/DiplomeRepository.php

    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.etablissement', 'e')
            ->leftJoin('e.pays', 'p')
            ->addSelect('e', 'p')               // précharge les relations
            ->orderBy('e.nom', 'ASC')
            ->addOrderBy('d.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }
    public function findWithRelations(int $id): ?Diplome
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.etablissement', 'e')
            ->addSelect('e')
            ->leftJoin('e.pays', 'p')
            ->addSelect('p')
            ->where('d.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
