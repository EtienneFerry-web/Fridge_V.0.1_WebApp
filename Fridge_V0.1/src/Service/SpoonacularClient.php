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
}