<?php

namespace App\Repository;

use App\Entity\Favori;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Favori>
 */
class FavoriRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Favori::class);
    }

        public function findFavoriIdsByUser(User $objUser): array
    {
        $arrResults = $this->createQueryBuilder('f')
            ->select('IDENTITY(f.favoriRecette) as recetteId')
            ->where('f.favoriUser = :user')
            ->setParameter('user', $objUser)
            ->getQuery()
            ->getArrayResult();

        $arrFavoris = [];
        foreach ($arrResults as $arrRow) {
            $arrFavoris[(int)$arrRow['recetteId']] = true;
        }

        return $arrFavoris;
    }
}
