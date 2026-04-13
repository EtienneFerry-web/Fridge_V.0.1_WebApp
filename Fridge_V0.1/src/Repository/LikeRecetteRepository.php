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
        return array_column(
            $this->createQueryBuilder('l')
                ->select('IDENTITY(l.likeRecette) as id')
                ->where('l.likeUser = :user')
                ->setParameter('user', $objUser)
                ->getQuery()
                ->getArrayResult(),
            'id'
        );
    }
}
