<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Client pour l'API TheMealDB (gratuit, clé publique "1").
 *
 * Stratégie : on charge TOUTES les recettes via search.php?s= (données complètes),
 * on les met en cache 24h, puis on filtre/pagine côté PHP.
 * Cela évite les appels multiples et donne accès à tous les champs sur chaque recette.
 */
class TheMealDbClient
{
    private const BASE_URL = 'https://www.themealdb.com/api/json/v1/1';

    // Lettres de l'alphabet pour agréger toutes les recettes
    private const LETTERS = ['a','b','c','d','e','f','g','h','i','j','k','l','m',
                              'n','o','p','q','r','s','t','u','v','w','x','y','z'];

    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface      $cache,
    ) {}

    /**
     * Retourne toutes les recettes disponibles avec leurs données complètes.
     * Mis en cache 24h — un seul jeu d'appels par jour.
     *
     * @return array<int, array> Tableau normalisé de toutes les recettes
     */
    public function getAllMeals(): array
    {
        return $this->cache->get('mealdb_all_meals', function (ItemInterface $item) {
            $item->expiresAfter(86400); // 24h

            $allMeals = [];

            foreach (self::LETTERS as $letter) {
                try {
                    $response = $this->httpClient->request('GET', self::BASE_URL . '/search.php', [
                        'query' => ['f' => $letter],
                    ]);
                    $data  = $response->toArray();
                    $meals = $data['meals'] ?? [];
                    foreach ($meals as $meal) {
                        $allMeals[] = $this->normalizeFull($meal);
                    }
                } catch (\Throwable) {
                    // On ignore les lettres sans résultat
                }
            }

            return $allMeals;
        });
    }

    /**
     * Recettes filtrées par catégorie et/ou origine.
     *
     * @param int    $limit    Nombre de recettes à retourner (0 = tous)
     * @param string $category Catégorie TheMealDB ('all' = toutes)
     * @param string $area     Origine/cuisine ('all' = toutes)
     */
    public function getFilteredMeals(int $limit = 0, string $category = 'all', string $area = 'all'): array
    {
        $all = $this->getAllMeals();

        if ($category !== 'all') {
            $all = array_values(array_filter($all, fn($m) => strcasecmp($m['category'], $category) === 0));
        }

        if ($area !== 'all') {
            $all = array_values(array_filter($all, fn($m) => strcasecmp($m['area'], $area) === 0));
        }

        return $limit > 0 ? array_slice($all, 0, $limit) : $all;
    }

    /**
     * Retourne la liste des origines disponibles (avec comptage).
     * Triées alphabétiquement, uniquement celles qui ont des recettes.
     *
     * @return array<int, array{area: string, count: int}>
     */
    public function getAvailableAreas(): array
    {
        $all    = $this->getAllMeals();
        $counts = [];
        foreach ($all as $meal) {
            $area = $meal['area'];
            if ($area) {
                $counts[$area] = ($counts[$area] ?? 0) + 1;
            }
        }
        ksort($counts);
        return array_map(fn($area, $count) => ['area' => $area, 'count' => $count], array_keys($counts), $counts);
    }

    /**
     * Retourne les catégories disponibles avec comptage.
     *
     * @return array<int, array{category: string, count: int}>
     */
    public function getAvailableCategories(): array
    {
        $all    = $this->getAllMeals();
        $counts = [];
        foreach ($all as $meal) {
            $cat = $meal['category'];
            if ($cat) {
                $counts[$cat] = ($counts[$cat] ?? 0) + 1;
            }
        }
        arsort($counts);
        return array_map(fn($cat, $count) => ['category' => $cat, 'count' => $count], array_keys($counts), $counts);
    }

    /** @deprecated Utiliser getFilteredMeals() */
    public function getDiscoveryMeals(int $limit = 24, string $category = 'all'): array
    {
        return $this->getFilteredMeals($limit, $category);
    }

    /**
     * Recherche par nom (retourne les données complètes).
     */
    public function searchByName(string $query): array
    {
        $cacheKey = 'mealdb_search_' . md5($query);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($query) {
            $item->expiresAfter(3600);

            $response = $this->httpClient->request('GET', self::BASE_URL . '/search.php', [
                'query' => ['s' => $query],
            ]);

            $data = $response->toArray();
            return array_map([$this, 'normalizeFull'], $data['meals'] ?? []);
        });
    }

    /**
     * Détail complet d'un repas par son ID (lookup.php).
     */
    public function getMealById(int $id): ?array
    {
        $cacheKey = "mealdb_meal_{$id}";

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($id) {
            $item->expiresAfter(86400);

            $response = $this->httpClient->request('GET', self::BASE_URL . '/lookup.php', [
                'query' => ['i' => $id],
            ]);

            $data  = $response->toArray();
            $meals = $data['meals'] ?? [];

            return !empty($meals) ? $this->normalizeFull($meals[0]) : null;
        });
    }

    /**
     * Liste de toutes les catégories disponibles.
     */
    public function getCategories(): array
    {
        return $this->cache->get('mealdb_categories', function (ItemInterface $item) {
            $item->expiresAfter(86400);

            $response = $this->httpClient->request('GET', self::BASE_URL . '/categories.php');
            $data     = $response->toArray();

            return array_map(fn($c) => [
                'name'  => $c['strCategory'],
                'image' => $c['strCategoryThumb'],
                'desc'  => $c['strCategoryDescription'],
            ], $data['categories'] ?? []);
        });
    }

    // ── Normalisation ──────────────────────────────────────────────────────────

    /**
     * Normalise un repas complet (search.php ou lookup.php) en tableau unifié.
     * Tous les champs sont présents même s'ils sont null.
     */
    private function normalizeFull(array $meal): array
    {
        // Ingrédients : strIngredient1-20 + strMeasure1-20
        $ingredients = [];
        for ($i = 1; $i <= 20; $i++) {
            $name    = trim((string) ($meal["strIngredient{$i}"] ?? ''));
            $measure = trim((string) ($meal["strMeasure{$i}"] ?? ''));
            if ($name !== '') {
                $ingredients[] = ['name' => $name, 'measure' => $measure];
            }
        }

        // Tags : "Meat,Casserole" → ['Meat', 'Casserole']
        $tags = [];
        if (!empty($meal['strTags'])) {
            $tags = array_filter(array_map('trim', explode(',', $meal['strTags'])));
        }

        return [
            'id'           => (int) ($meal['idMeal'] ?? 0),
            'title'        => (string) ($meal['strMeal'] ?? ''),
            'titleAlt'     => (string) ($meal['strMealAlternate'] ?? ''),
            'image'        => (string) ($meal['strMealThumb'] ?? ''),
            'category'     => (string) ($meal['strCategory'] ?? ''),
            'area'         => (string) ($meal['strArea'] ?? ''),
            'country'      => (string) ($meal['strCountry'] ?? ''),
            'instructions' => (string) ($meal['strInstructions'] ?? ''),
            'tags'         => array_values($tags),
            'youtube'      => (string) ($meal['strYoutube'] ?? ''),
            'source'       => (string) ($meal['strSource'] ?? ''),
            'imageSource'  => (string) ($meal['strImageSource'] ?? ''),
            'dateModified' => (string) ($meal['dateModified'] ?? ''),
            'ingredients'  => $ingredients,
        ];
    }
}
