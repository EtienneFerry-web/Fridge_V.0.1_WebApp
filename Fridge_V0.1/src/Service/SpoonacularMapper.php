<?php

namespace App\Service;

use App\Entity\Regime;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service de mapping des données Spoonacular vers les valeurs de l'application.
 *
 * Centralise les correspondances entre :
 * - Les noms de cuisine Spoonacular et tes codes recetteOrigine ('it', 'fr', etc.)
 * - Les flags de régime Spoonacular (vegetarian/vegan/etc.) et tes entités Regime
 *
 * Séparé de RecetteImporter pour la lisibilité et la testabilité.
 */
class SpoonacularMapper
{
    /**
     * Correspondance entre les libellés de cuisine Spoonacular et les codes recetteOrigine.
     * Une recette Spoonacular peut avoir plusieurs cuisines : on prend la première qui match.
     */
    private const CUISINE_MAPPING = [
        'Italian'        => 'it',
        'French'         => 'fr',
        'Mexican'        => 'mx',
        'Spanish'        => 'es',
        'Greek'          => 'gr',
        'Indian'         => 'in',
        'American'       => 'us',
        'Southern'       => 'us',
        'Cajun'          => 'us',
        'Japanese'       => 'asia',
        'Chinese'        => 'asia',
        'Thai'           => 'asia',
        'Korean'         => 'asia',
        'Vietnamese'     => 'asia',
        'Asian'          => 'asia',
        'Middle Eastern' => 'me',
        'Mediterranean'  => 'me',
        'Moroccan'       => 'af',
        'African'        => 'af',
        'British'        => 'uk',
        'Irish'          => 'uk',
        'Scottish'       => 'uk',
        'German'         => 'de',
        'Nordic'         => 'nordic',
    ];

    /**
     * Libellés exacts des régimes en BDD (doivent matcher la table 'regime').
     */
    private const REGIME_OMNIVORE     = 'Omnivore';
    private const REGIME_VEGETARIEN   = 'Végétarien';
    private const REGIME_VEGAN        = 'Vegan';
    private const REGIME_SANS_GLUTEN  = 'Sans gluten';
    private const REGIME_SANS_LACTOSE = 'Sans lactose';

    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    /**
     * Convertit un tableau de cuisines Spoonacular en code recetteOrigine.
     *
     * Retourne null si aucune cuisine ne correspond à un code connu.
     *
     * @param string[] $arrCuisines Liste des cuisines Spoonacular (ex: ['Italian', 'Mediterranean'])
     */
    public function mapCuisineToOrigine(array $arrCuisines): ?string
    {
        foreach ($arrCuisines as $strCuisine) {
            if (isset(self::CUISINE_MAPPING[$strCuisine])) {
                return self::CUISINE_MAPPING[$strCuisine];
            }
        }

        return null;
    }

    /**
     * Convertit les flags de régime Spoonacular en entités Regime.
     *
     * Logique :
     * - vegan: true       → Vegan + Végétarien (un vegan est aussi végétarien)
     * - vegetarian: true  → Végétarien
     * - glutenFree: true  → Sans gluten
     * - dairyFree: true   → Sans lactose
     * - Aucun flag        → Omnivore (par défaut)
     *
     * @return Regime[] Liste des entités Regime correspondantes
     */
    public function mapDietsToRegimes(array $arrSpoonacularData): array
    {
        $arrLibelles = [];

        if (!empty($arrSpoonacularData['vegan'])) {
            $arrLibelles[] = self::REGIME_VEGAN;
            $arrLibelles[] = self::REGIME_VEGETARIEN; // implicite
        } elseif (!empty($arrSpoonacularData['vegetarian'])) {
            $arrLibelles[] = self::REGIME_VEGETARIEN;
        }

        if (!empty($arrSpoonacularData['glutenFree'])) {
            $arrLibelles[] = self::REGIME_SANS_GLUTEN;
        }

        if (!empty($arrSpoonacularData['dairyFree'])) {
            $arrLibelles[] = self::REGIME_SANS_LACTOSE;
        }

        // Si aucun flag spécifique, marquer comme Omnivore par défaut
        if (empty($arrLibelles)) {
            $arrLibelles[] = self::REGIME_OMNIVORE;
        }

        // Récupérer les entités Regime correspondantes (uniques)
        $arrLibelles = array_unique($arrLibelles);
        $arrRegimes = [];

        foreach ($arrLibelles as $strLibelle) {
            $objRegime = $this->em->getRepository(Regime::class)
                ->findOneBy(['regimeLibelle' => $strLibelle]);

            if ($objRegime instanceof Regime) {
                $arrRegimes[] = $objRegime;
            }
        }

        return $arrRegimes;
    }

    /**
     * Convertit un libellé de régime BDD en paramètres pour Spoonacular complexSearch.
     *
     * Spoonacular distingue 'diet' (vegan/vegetarian) et 'intolerances' (gluten/dairy),
     * d'où le retour d'un tableau associatif qui peut contenir l'une ou l'autre clé.
     *
     * @param string $strRegime Libellé exact du régime BDD ou 'all' pour aucun filtre
     *
     * @return array{diet?: string, intolerances?: string} Paramètres à fusionner dans la query API
     */
    public function mapRegimeToApiParams(string $strRegime): array
    {
        return match ($strRegime) {
            self::REGIME_VEGETARIEN   => ['diet' => 'vegetarian'],
            self::REGIME_VEGAN        => ['diet' => 'vegan'],
            self::REGIME_SANS_GLUTEN  => ['intolerances' => 'gluten'],
            self::REGIME_SANS_LACTOSE => ['intolerances' => 'dairy'],
            default                   => [], // 'Omnivore', 'all', ou inconnu → pas de filtre
        };
    }
}