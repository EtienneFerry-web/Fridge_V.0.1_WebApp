<?php

namespace App\Repository;

use App\Entity\Recette;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recette>
 */
class RecetteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recette::class);
    }

    //    /**
    //     * @return Recette[] Returns an array of Recette objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Recette
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findBySearch(
        string $strQuery,
        array $arrDifficulte,
        array $arrRegime,
        string $strOrigine,
        int $intTempsMax,
        string $strSort
    ): array {
        $qb = $this->createQueryBuilder('r')
            ->where('r.recetteStatut = :statut')
            ->setParameter('statut', 'publie');

        if ($strQuery !== '') {
            $qb->andWhere('r.recetteLibelle LIKE :q OR r.recetteDescription LIKE :q')
            ->setParameter('q', '%' . $strQuery . '%');
        }

        if (!empty($arrDifficulte)) {
            $qb->andWhere('r.recetteDifficulte IN (:difficulte)')
            ->setParameter('difficulte', $arrDifficulte);
        }

        if (!empty($arrRegime)) {
            $qb->join('r.regimes', 'reg')
            ->andWhere('reg.id IN (:regimes)')
            ->setParameter('regimes', $arrRegime);
        }

        if ($strOrigine !== '') {
            $qb->andWhere('r.recetteOrigine = :origine')
            ->setParameter('origine', $strOrigine);
        }

        // Temps total = prépa + cuisson
        $qb->andWhere('(r.recetteTempsPrepa + r.recetteTempsCuisson) <= :tempsMax')
        ->setParameter('tempsMax', $intTempsMax);

        match ($strSort) {
            'recent'   => $qb->orderBy('r.recetteCreatedAt', 'DESC'),
            'time'     => $qb->orderBy('r.recetteTempsPrepa + r.recetteTempsCuisson', 'ASC'),
            default    => $qb->orderBy('r.recetteCreatedAt', 'DESC'),
        };

        return $qb->getQuery()->getResult();
    }
}
