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
 * IMPORTANT — Logique de visibilité :
 * - Recettes 'spoonacular' : publiques, visibles par tous
 * - Recettes 'user' : privées (statut 'prive'), visibles uniquement par leur créateur
 * - Recettes 'en_attente' / 'refuse' : statuts historiques (modération désactivée)
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
     * Conservée pour le dashboard d'administration.
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
     * Retourne les recettes créées par un utilisateur donné, avec filtres optionnels.
     *
     * Utilisée pour la page "Mes recettes" — affiche uniquement les créations
     * privées de l'utilisateur connecté.
     *
     * @param User   $objUser   L'utilisateur dont on veut les recettes
     * @param string $strRegime Libellé du régime alimentaire ('all' = pas de filtre)
     * @param string $strSort   Critère de tri ('recent' | 'popular')
     *
     * @return Recette[]
     */
    public function findUserRecettes(User $objUser, string $strRegime = 'all', string $strSort = 'recent'): array
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.regimes', 'reg')
            ->where('r.createdBy = :user')
            ->andWhere('r.recetteSource = :source')
            ->setParameter('user', $objUser)
            ->setParameter('source', 'user');

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

    /**
     * Recherche multicritères dans les recettes accessibles à l'utilisateur.
     *
     * Retourne uniquement les recettes auxquelles l'user a légitimement accès :
     * ses propres recettes (peu importe le statut) ainsi que les recettes Spoonacular
     * importées en BDD (statut 'publie' & source 'spoonacular').
     *
     * @param User|null $objUser       L'utilisateur connecté (null = recherche publique uniquement)
     * @param string    $strQuery      Terme de recherche sur le libellé
     * @param string[]  $arrDifficulte Filtres de difficulté
     * @param string[]  $arrRegime     Identifiants de régimes alimentaires
     * @param string    $strOrigine    Origine géographique exacte
     * @param int       $intTempsMax   Temps total maximum en minutes
     * @param string    $strSort       Critère de tri ('recent' | 'time')
     *
     * @return Recette[]
     */
    public function findBySearch(
        ?User  $objUser,
        string $strQuery,
        array  $arrDifficulte,
        array  $arrRegime,
        string $strOrigine,
        int    $intTempsMax,
        string $strSort
    ): array {
        $qb = $this->createQueryBuilder('r');

        // Visibilité : recettes Spoonacular publiques OU recettes de l'user connecté
        if ($objUser !== null) {
            $qb->where('(r.recetteSource = :spoonacular AND r.recetteStatut = :publie) OR r.createdBy = :user')
               ->setParameter('spoonacular', 'spoonacular')
               ->setParameter('publie', 'publie')
               ->setParameter('user', $objUser);
        } else {
            // Visiteur non connecté : uniquement les recettes Spoonacular publiques
            $qb->where('r.recetteSource = :spoonacular AND r.recetteStatut = :publie')
               ->setParameter('spoonacular', 'spoonacular')
               ->setParameter('publie', 'publie');
        }

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

        // Filtre temps max — uniquement si les champs sont renseignés
        // (les recettes Spoonacular pourraient avoir des temps null)
        $qb->andWhere('(COALESCE(r.recetteTempsPrepa, 0) + COALESCE(r.recetteTempsCuisson, 0)) <= :tempsMax')
           ->setParameter('tempsMax', $intTempsMax);

        match ($strSort) {
            'time'  => $qb->addSelect('(COALESCE(r.recetteTempsPrepa, 0) + COALESCE(r.recetteTempsCuisson, 0)) AS HIDDEN tempsTotal')
                          ->orderBy('tempsTotal', 'ASC'),
            default => $qb->orderBy('r.recetteCreatedAt', 'DESC'),
        };

        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne les recettes Spoonacular publiées en BDD pour la page liste publique.
     *
     * Note : la vraie page "Découvrir" appelle Spoonacular en direct (pas la BDD).
     * Cette méthode sert pour les recettes Spoonacular sauvegardées par les users
     * (visibles publiquement) ou pour des cas comme une page "tendances".
     *
     * @return Recette[]
     */
    public function findWithFilters(string $strRegime = 'all', string $strSort = 'recent'): array
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.regimes', 'reg')
            ->where('r.recetteSource = :source')
            ->andWhere('r.recetteStatut = :statut')
            ->setParameter('source', 'spoonacular')
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

        return $qb->getQuery()->getResult();
    }

    /**
     * Variante de findWithFilters retournant un Query Doctrine pour pagination KnpPaginator.
     */
    public function createQueryBuilderWithFilters(string $strRegime = 'all', string $strSort = 'recent'): \Doctrine\ORM\Query
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.regimes', 'reg')
            ->where('r.recetteSource = :source')
            ->andWhere('r.recetteStatut = :statut')
            ->setParameter('source', 'spoonacular')
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