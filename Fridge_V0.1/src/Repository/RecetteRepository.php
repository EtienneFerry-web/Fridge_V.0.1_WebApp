<?php

namespace App\Repository;

use App\Entity\LikeRecette;
use App\Entity\Recette;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository des recettes.
 *
 * Fournit des requêtes DQL personnalisées pour la recherche multicritères,
 * les filtres de la page liste et les stats du dashboard.
 *
 * @extends ServiceEntityRepository<Recette>
 */
class RecetteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recette::class);
    }

    /**
     * Retourne toutes les recettes correspondant à un statut donné.
     *
     * Utilisée par le dashboard pour lister les recettes en attente ('en_attente'),
     * publiées ('publie') ou refusées ('refuse').
     *
     * @param string $strStatut Statut de modération ('en_attente' | 'publie' | 'refuse')
     *
     * @return Recette[]
     */
    public function findByStatut(string $strStatut): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.recetteStatut = :statut')
            ->setParameter('statut', $strStatut)
            ->orderBy('r.recetteCreatedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les recettes likées par un utilisateur avec le nombre total de likes de chaque recette.
     *
     * @return array{recette: Recette, likeCount: int}[]
     */
    public function findLikedByUserWithCount(User $objUser): array
    {
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

    /**
     * Recherche multicritères des recettes publiées pour la page de recherche.
     *
     * Seules les recettes au statut 'publie' sont retournées — les recettes
     * en attente ou refusées sont exclues.
     *
     * @param string   $strQuery      Terme de recherche sur le libellé (partiel, insensible à la casse)
     * @param string[] $arrDifficulte Filtres de difficulté (ex. ['Facile', 'Moyen'])
     * @param string[] $arrRegime     Identifiants de régimes alimentaires
     * @param string   $strOrigine    Origine géographique exacte
     * @param int      $intTempsMax   Temps total maximum en minutes (prépa + cuisson)
     * @param string   $strSort       Critère de tri ('recent' | 'time')
     *
     * @return Recette[]
     */
    public function findBySearch(
        string $strQuery,
        array  $arrDifficulte,
        array  $arrRegime,
        string $strOrigine,
        int    $intTempsMax,
        string $strSort
    ): array {
        $qb = $this->createQueryBuilder('r')
            ->where('r.recetteStatut = :statut')
            ->setParameter('statut', 'publie');  // ← seules les recettes modérées

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

        $qb->andWhere('(r.recetteTempsPrepa + r.recetteTempsCuisson) <= :tempsMax')
           ->setParameter('tempsMax', $intTempsMax);

        match ($strSort) {
            'time'  => $qb->addSelect('(r.recetteTempsPrepa + r.recetteTempsCuisson) AS HIDDEN tempsTotal')
                          ->orderBy('tempsTotal', 'ASC'),
            default => $qb->orderBy('r.recetteCreatedAt', 'DESC'),
        };

        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne les recettes publiées avec un filtre de régime et un tri pour la page liste principale.
     *
     * Seules les recettes au statut 'publie' sont retournées — les recettes
     * en attente ou refusées sont exclues.
     *
     * Tri 'popular' : par nombre de likes décroissant. Tout autre valeur : par date décroissante.
     *
     * @param string $strRegime Libellé du régime alimentaire ('all' = pas de filtre)
     * @param string $strSort   Critère de tri ('recent' | 'popular')
     *
     * @return Recette[]
     */
    public function findWithFilters(string $strRegime = 'all', string $strSort = 'recent'): array
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.regimes', 'reg')
            ->where('r.recetteStatut = :statut')
            ->setParameter('statut', 'publie');  // ← seules les recettes modérées

        if ($strRegime !== 'all') {
            $qb->andWhere('reg.regimeLibelle = :regime')
               ->setParameter('regime', $strRegime);
        }

        match ($strSort) {
            'popular' => $qb->leftJoin('r.likeRecettes', 'lr')
                            ->addSelect('COUNT(lr.id) AS HIDDEN likeCount')
                            ->groupBy('r.id')
                            ->orderBy('likeCount', 'DESC'),
            default   => $qb->orderBy('r.recetteCreatedAt', 'DESC'),
        };

        return $qb->getQuery()->getResult();
    }

    public function createQueryBuilderWithFilters(string $strRegime = 'all', string $strSort = 'recent'): \Doctrine\ORM\Query
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.regimes', 'reg')
            ->where('r.recetteStatut = :statut')
            ->setParameter('statut', 'publie');

        if ($strRegime !== 'all') {
            $qb->andWhere('reg.regimeLibelle = :regime')
               ->setParameter('regime', $strRegime);
        }

        match ($strSort) {
            'popular' => $qb->leftJoin('r.likeRecettes', 'lr')
                            ->addSelect('COUNT(lr.id) AS HIDDEN likeCount')
                            ->groupBy('r.id')
                            ->orderBy('likeCount', 'DESC'),
            default   => $qb->orderBy('r.recetteCreatedAt', 'DESC'),
        };

        return $qb->getQuery();
    }
}