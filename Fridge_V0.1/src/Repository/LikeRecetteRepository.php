<?php

namespace App\Repository;

use App\Entity\LikeRecette;
use App\Entity\Recette;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LikeRecette>
 */
class LikeRecetteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LikeRecette::class);
    }

    public function findLikedIdsByUser(User $objUser): array
    {
        $arrResults = $this->createQueryBuilder('l')
            ->select('IDENTITY(l.likeRecette) as recetteId')
            ->where('l.likeUser = :user')
            ->setParameter('user', $objUser)
            ->getQuery()
            ->getArrayResult();

        $arrLiked = [];
        foreach ($arrResults as $arrRow) {
            $arrLiked[(int)$arrRow['recetteId']] = true;
        }

        return $arrLiked;
    }

    public function findLikedRecettesByUser(User $objUser): array
    {
        $objEm = $this->getEntityManager();
        return $objEm->createQueryBuilder()
            ->select('r')
            ->from(\App\Entity\Recette::class, 'r')
            ->join(\App\Entity\LikeRecette::class, 'l', 'WITH', 'l.likeRecette = r')
            ->where('l.likeUser = :user')
            ->setParameter('user', $objUser)
            ->getQuery()
            ->getResult();
    }

    public function findTopLikedRecettes(int $intLimit = 5): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('r', 'COUNT(l.id) as likeCount')
            ->from(\App\Entity\Recette::class, 'r')
            ->join(\App\Entity\LikeRecette::class, 'l', 'WITH', 'l.likeRecette = r')
            ->groupBy('r.id')
            ->orderBy('likeCount', 'DESC')
            ->setMaxResults($intLimit)
            ->getQuery()
            ->getResult();
    }
}