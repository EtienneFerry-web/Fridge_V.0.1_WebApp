<?php

namespace App\Repository;

use App\Entity\Favori;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository des favoris.
 *
 * Fournit des requêtes pour récupérer les favoris d'un utilisateur sous différentes formes (ids ou entités Recette).
 *
 * @extends ServiceEntityRepository<Favori>
 */
class FavoriRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Favori::class);
    }

    /**
     * Retourne un tableau associatif [recetteId => true] des recettes mises en favori par un utilisateur.
     *
     * Structure optimisée pour un accès O(1) lors de l'affichage des boutons favori.
     *
     * @return array<int, bool>
     */
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

    /**
     * Retourne les entités Recette mises en favori par un utilisateur (utilisé dans le planning).
     *
     * @return Recette[]
     */
    public function findFavoriRecettesByUser(User $objUser): array
    {
        $objEm = $this->getEntityManager();
        return $objEm->createQueryBuilder()
            ->select('r')
            ->from(\App\Entity\Recette::class, 'r')
            ->join(\App\Entity\Favori::class, 'f', 'WITH', 'f.favoriRecette = r')
            ->where('f.favoriUser = :user')
            ->setParameter('user', $objUser)
            ->getQuery()
            ->getResult();
    }
}