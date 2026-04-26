<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Client pour l'API Spoonacular.
 * Encapsule les appels HTTP avec mise en cache pour économiser les quotas.
 */
class SpoonacularClient
{
    public function __construct(
        private HttpClientInterface $spoonacularClient,
        private CacheInterface $cache,
    ) {}

    /**
     * Trouve des recettes à partir d'une liste d'ingrédients.
     * 
     * @param array $ingredients Liste d'ingrédients en anglais (ex: ['chicken', 'onion'])
     * @param int $number Nombre de recettes à retourner (max 100)
     * @param int $ranking 1 = maximise les ingrédients utilisés, 2 = minimise les manquants
     * @param bool $ignorePantry Ignore les ingrédients de base (sel, eau, etc.)
     */
    public function findRecipesByIngredients(
        array $ingredients,
        int $number = 10,
        int $ranking = 1,
        bool $ignorePantry = true
    ): array {
        sort($ingredients);
        $cacheKey = 'spoon_find_' . md5(implode(',', $ingredients) . "_{$number}_{$ranking}");

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($ingredients, $number, $ranking, $ignorePantry) {
            $item->expiresAfter(86400); // 24h

            $response = $this->spoonacularClient->request('GET', '/recipes/findByIngredients', [
                'query' => [
                    'ingredients' => implode(',', $ingredients),
                    'number' => $number,
                    'ranking' => $ranking,
                    'ignorePantry' => $ignorePantry ? 'true' : 'false',
                ],
            ]);

            return $response->toArray();
        });
    }

    /**
     * Récupère les informations détaillées d'une recette.
     */
    public function getRecipeInformation(int $recipeId, bool $includeNutrition = false): array
    {
        $cacheKey = "spoon_recipe_{$recipeId}_" . ($includeNutrition ? '1' : '0');

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($recipeId, $includeNutrition) {
            $item->expiresAfter(604800); // 7 jours

            $response = $this->spoonacularClient->request('GET', "/recipes/{$recipeId}/information", [
                'query' => ['includeNutrition' => $includeNutrition ? 'true' : 'false'],
            ]);

            return $response->toArray();
        });
    }

    /**
     * Autocomplétion d'ingrédients (utile pour le formulaire d'ajout au frigo).
     */
    public function autocompleteIngredient(string $query, int $number = 10): array
    {
        return $this->cache->get('spoon_auto_' . md5("{$query}_{$number}"), function (ItemInterface $item) use ($query, $number) {
            $item->expiresAfter(2592000); // 30 jours

            $response = $this->spoonacularClient->request('GET', '/food/ingredients/autocomplete', [
                'query' => ['query' => $query, 'number' => $number],
            ]);

            return $response->toArray();
        });
    }

    /**
     * Recherche avancée de recettes avec filtres et tri (catalogue de découverte).
     *
     * Utilisé par la liste publique /recette. Supporte régime, intolérances, tri et pagination.
     *
     * @param int    $intNumber Nombre de recettes à retourner (max 100)
     * @param int    $intOffset Décalage pour pagination (0 = première page)
     * @param string $strSort   Tri Spoonacular : 'popularity', 'healthiness', 'time', 'random', 'meta-score'
     * @param array  $arrFilters Filtres additionnels : ['diet' => 'vegan', 'intolerances' => 'gluten', 'cuisine' => 'italian', ...]
     *
     * @return array Réponse Spoonacular avec clés : results, offset, number, totalResults
     */
    public function complexSearch(
        int $intNumber = 12,
        int $intOffset = 0,
        string $strSort = 'popularity',
        array $arrFilters = []
    ): array {
        ksort($arrFilters); // pour stabiliser la clé de cache
        $strCacheKey = 'spoon_search_' . md5(serialize([$intNumber, $intOffset, $strSort, $arrFilters]));

        return $this->cache->get($strCacheKey, function (ItemInterface $item) use ($intNumber, $intOffset, $strSort, $arrFilters) {
            $item->expiresAfter(3600); // 1h : la liste publique évolue, on ne sur-cache pas

            $arrQuery = array_merge($arrFilters, [
                'number'             => $intNumber,
                'offset'             => $intOffset,
                'sort'               => $strSort,
                'addRecipeInformation' => 'true', // pour avoir image, readyInMinutes, servings dans les résultats
            ]);

            $objResponse = $this->spoonacularClient->request('GET', '/recipes/complexSearch', [
                'query' => $arrQuery,
            ]);

            return $objResponse->toArray();
        });
    }

    /**
     * Récupère un lot de recettes aléatoires (mode "surprends-moi").
     *
     * Pas de pagination ni de tri — utile pour une page d'inspiration.
     *
     * @param int      $intNumber Nombre de recettes (max 100)
     * @param string[] $arrTags   Tags Spoonacular (cuisines, régimes, types de plat) — joints par virgule
     */
    public function getRandomRecipes(int $intNumber = 12, array $arrTags = []): array
    {
        sort($arrTags);
        $strCacheKey = 'spoon_random_' . md5($intNumber . '_' . implode(',', $arrTags));

        return $this->cache->get($strCacheKey, function (ItemInterface $item) use ($intNumber, $arrTags) {
            $item->expiresAfter(1800); // 30min : on veut un peu de variété mais sans cramer le quota

            $arrQuery = ['number' => $intNumber];
            if (!empty($arrTags)) {
                $arrQuery['tags'] = implode(',', $arrTags);
            }

            $objResponse = $this->spoonacularClient->request('GET', '/recipes/random', [
                'query' => $arrQuery,
            ]);

            return $objResponse->toArray();
        });
    }
}