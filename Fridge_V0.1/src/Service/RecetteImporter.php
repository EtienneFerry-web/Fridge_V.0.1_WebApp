<?php

namespace App\Service;

use App\Entity\Contenir;
use App\Entity\Etape;
use App\Entity\Recette;
use App\Repository\RecetteRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service d'import de recettes depuis Spoonacular vers la BDD locale.
 *
 * Quand un utilisateur "sauvegarde" une recette Spoonacular pour la mettre dans ses listes
 * ou favoris, on l'importe en BDD avec source='spoonacular' et statut='publie'.
 * Le createdBy est le user système Spoonacular (cf. SystemUserProvider).
 *
 * Anti-doublon : si une recette avec le même spoonacularId existe déjà, on retourne
 * l'instance existante au lieu d'en créer une nouvelle.
 */
class RecetteImporter
{
    public function __construct(
        private SpoonacularClient    $spoonacularClient,
        private SpoonacularMapper    $mapper,
        private SystemUserProvider   $systemUserProvider,
        private RecetteRepository    $recetteRepository,
        private EntityManagerInterface $em,
    ) {}

    /**
     * Importe une recette Spoonacular en BDD ou retourne l'existante si déjà importée.
     *
     * @param int $intSpoonacularId Identifiant Spoonacular de la recette
     *
     * @throws \Throwable Si l'API échoue ou si les données sont invalides
     */
    public function importFromSpoonacular(int $intSpoonacularId): Recette
    {
        // 1. Anti-doublon : la recette existe-t-elle déjà ?
        $objExisting = $this->recetteRepository->findOneBy([
            'spoonacularId' => $intSpoonacularId,
        ]);

        if ($objExisting instanceof Recette) {
            return $objExisting;
        }

        // 2. Récupération des données depuis l'API
        $arrData = $this->spoonacularClient->getRecipeInformation($intSpoonacularId);

        // 3. Création de l'entité Recette
        $objRecette = new Recette();
        $objRecette->setRecetteSource('spoonacular')
                   ->setSpoonacularId($intSpoonacularId)
                   ->setSourceUrl($arrData['sourceUrl'] ?? null)
                   ->setRecetteStatut('publie') // Spoonacular = directement publique
                   ->setCreatedBy($this->systemUserProvider->getSpoonacularUser())
                   ->setRecetteLibelle($arrData['title'] ?? 'Sans titre')
                   ->setRecetteDescription($this->cleanDescription($arrData['summary'] ?? null))
                   ->setRecettePortion($arrData['servings'] ?? null)
                   ->setRecetteTempsPrepa(0) // Spoonacular ne sépare pas prépa/cuisson
                   ->setRecetteTempsCuisson($arrData['readyInMinutes'] ?? null)
                   ->setRecetteOrigine($this->mapper->mapCuisineToOrigine($arrData['cuisines'] ?? []))
                   ->setRecettePhoto($arrData['image'] ?? null); // URL Spoonacular complète

        // 4. Régimes alimentaires
        foreach ($this->mapper->mapDietsToRegimes($arrData) as $objRegime) {
            $objRecette->addRegime($objRegime);
        }

        // 5. Étapes (depuis analyzedInstructions)
        $this->importEtapes($objRecette, $arrData['analyzedInstructions'] ?? []);

        // 6. Ingrédients (stockés en brut dans contenir_libelle_brut)
        $this->importIngredients($objRecette, $arrData['extendedIngredients'] ?? []);

        // 7. Persistance
        $this->em->persist($objRecette);
        $this->em->flush();

        return $objRecette;
    }

    /**
     * Crée les entités Etape depuis les analyzedInstructions Spoonacular.
     */
    private function importEtapes(Recette $objRecette, array $arrAnalyzedInstructions): void
    {
        // Spoonacular peut renvoyer plusieurs blocs d'instructions (ex: "Crust", "Filling")
        // On les concatène en une seule séquence numérotée
        $intNumero = 1;

        foreach ($arrAnalyzedInstructions as $arrBloc) {
            foreach ($arrBloc['steps'] ?? [] as $arrStep) {
                $strDescription = trim($arrStep['step'] ?? '');
                if ($strDescription === '') {
                    continue;
                }

                $objEtape = new Etape();
                $objEtape->setEtapeNumero($intNumero++)
                         ->setEtapeLibelle('Étape ' . ($intNumero - 1)) // Spoonacular ne fournit pas de titre
                         ->setEtapeDescription($strDescription)
                         ->setEtapeDuree(null); // Spoonacular ne fournit pas de durée par étape
                $objRecette->addEtape($objEtape);
            }
        }
    }

    /**
     * Crée les entités Contenir avec libellé brut depuis les extendedIngredients.
     *
     * Note : on ne crée pas d'entités Ingredient pour l'instant (mapping FR/EN reporté
     * au point 5 de la roadmap). Les ingrédients sont stockés en texte brut dans
     * contenirLibelleBrut, ce qui permet l'affichage immédiat sans BDD complexe.
     */
    private function importIngredients(Recette $objRecette, array $arrExtendedIngredients): void
    {
        foreach ($arrExtendedIngredients as $arrIngredient) {
            $strNom    = trim($arrIngredient['name'] ?? '');
            $fltAmount = (float) ($arrIngredient['amount'] ?? 0);
            $strUnit   = trim($arrIngredient['unit'] ?? '');

            if ($strNom === '') {
                continue;
            }

            $objContenir = new Contenir();
            $objContenir->setContenirLibelleBrut($strNom)
                        ->setContenirQuantite($fltAmount > 0 ? $fltAmount : null)
                        ->setContenirUnite($strUnit !== '' ? $strUnit : null)
                        ->setContenirEstCoche(false);

            $objRecette->addContenir($objContenir);
        }
    }

    /**
     * Nettoie la description HTML de Spoonacular (le summary contient des balises HTML
     * et des liens promotionnels). On retire le HTML pour avoir un texte propre.
     */
    private function cleanDescription(?string $strHtmlSummary): ?string
    {
        if ($strHtmlSummary === null) {
            return null;
        }

        // Strip tags HTML
        $strClean = strip_tags($strHtmlSummary);

        // Décoder les entités HTML (&nbsp; → espace, etc.)
        $strClean = html_entity_decode($strClean, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Réduire les espaces multiples
        $strClean = preg_replace('/\s+/', ' ', $strClean);
        $strClean = trim($strClean);

        // Limiter à 1000 caractères (résumés Spoonacular peuvent être très longs)
        if (mb_strlen($strClean) > 1000) {
            $strClean = mb_substr($strClean, 0, 997) . '...';
        }

        return $strClean !== '' ? $strClean : null;
    }
}