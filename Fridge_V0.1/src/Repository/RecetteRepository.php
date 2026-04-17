<?php

namespace App\Repository;

use App\Entity\LikeRecette;
use App\Entity\Recette;
use App\Entity\User;
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

    public function findLikedByUserWithCount(User $objUser): array
    {
        // r = Recette (racine), l = like de l'utilisateur, l2 = tous les likes de la recette
        $results = $this->createQueryBuilder('r')
            ->select('r', 'COUNT(l2.id) as likeCount')
            ->innerJoin(LikeRecette::class, 'l',  'WITH', 'l.likeRecette = r AND l.likeUser = :user')
            ->leftJoin( LikeRecette::class, 'l2', 'WITH', 'l2.likeRecette = r')
            ->setParameter('user', $objUser)
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();

        return array_map(fn($row) => [
            'recette'   => $row[0],
            'likeCount' => (int) $row['likeCount'],
        ], $results);
    }

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
            $qb->andWhere('LOWER(r.recetteLibelle) LIKE LOWER(:q)')
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
            'recent' => $qb->orderBy('r.recetteCreatedAt', 'DESC'),
            'time'   => $qb->addSelect('(r.recetteTempsPrepa + r.recetteTempsCuisson) AS HIDDEN tempsTotal')
                        ->orderBy('tempsTotal', 'ASC'),
            default  => $qb->orderBy('r.recetteCreatedAt', 'DESC'),
        };

        return $qb->getQuery()->getResult();
    }

    public function findWithFilters(string $regime = 'all', string $sort = 'recent'): array
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.regimes', 'reg')
            ->where('r.recetteStatut = :statut')
            ->setParameter('statut', 'publie');

        if ($regime !== 'all') {
            $qb->andWhere('reg.regimeLibelle = :regime')
            ->setParameter('regime', $regime);
        }

        match ($sort) {
            'popular' => $qb->leftJoin('r.likeRecettes', 'lr')
                            ->addSelect('COUNT(lr.id) AS HIDDEN likeCount')
                            ->groupBy('r.id')
                            ->orderBy('likeCount', 'DESC'),
            default   => $qb->orderBy('r.recetteCreatedAt', 'DESC'),
        };

        return $qb->getQuery()->getResult();
    }
}
