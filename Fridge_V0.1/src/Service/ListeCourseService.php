<?php

namespace App\Service;

use App\Entity\Contenir;
use App\Entity\ListeCourse;
use App\Entity\User;
use App\Repository\PlanningRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service de génération de listes de courses à partir du planning hebdomadaire.
 *
 * Agrège les ingrédients de toutes les recettes planifiées par l'utilisateur.
 * Les quantités sont additionnées par ingrédient si les unités sont identiques ;
 * sinon des lignes séparées sont créées.
 */
class ListeCourseService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PlanningRepository     $planningRepository,
    ) {}

    /**
     * Génère (ou régénère) une ListeCourse à partir de toutes les recettes
     * du planning de l'utilisateur connecté.
     *
     * Les ingrédients communs à plusieurs recettes sont additionnés
     * si et seulement si leur unité est identique. Si les unités diffèrent
     * (ex: 3 tbsp + 200 g d'huile d'olive), deux lignes séparées sont créées.
     */
    public function genererDepuisPlanning(User $user): ListeCourse
    {
        // 1. Récupérer tous les plannings de l'utilisateur avec leurs recettes et ingrédients
        $plannings = $this->planningRepository->findBy(['planningUser' => $user]);

        // 2. Agréger les quantités : clé = ingredientId + '|' + unite
        $agregat = [];

        foreach ($plannings as $planning) {
            $recette = $planning->getPlanningRecette();
            if ($recette === null) {
                continue;
            }

            // Charger les Contenir liés à la recette (listeCourse = null = template)
            foreach ($recette->getContenirs() as $contenir) {
                // On ne prend que les lignes "template recette" (pas déjà dans une liste)
                if ($contenir->getListeCourse() !== null) {
                    continue;
                }

                $ingredient    = $contenir->getIngredient();
                $libelleBrut   = $contenir->getContenirLibelleBrut();

                // Recette locale : ingrédient en BDD
                if ($ingredient !== null) {
                    $unite  = $contenir->getContenirUnite() ?? '';
                    $cleAgg = 'id_' . $ingredient->getId() . '|' . $unite;

                    if (!isset($agregat[$cleAgg])) {
                        $agregat[$cleAgg] = [
                            'ingredient'  => $ingredient,
                            'libelleBrut' => null,
                            'quantite'    => 0,
                            'unite'       => $contenir->getContenirUnite(),
                        ];
                    }

                    $agregat[$cleAgg]['quantite'] += $contenir->getContenirQuantite() ?? 0;

                // Recette Spoonacular : ingrédient stocké comme libellé brut
                } elseif ($libelleBrut !== null && $libelleBrut !== '') {
                    $unite  = $contenir->getContenirUnite() ?? '';
                    $cleAgg = 'brut_' . mb_strtolower($libelleBrut) . '|' . $unite;

                    if (!isset($agregat[$cleAgg])) {
                        $agregat[$cleAgg] = [
                            'ingredient'  => null,
                            'libelleBrut' => $libelleBrut,
                            'quantite'    => 0,
                            'unite'       => $contenir->getContenirUnite(),
                        ];
                    }

                    $agregat[$cleAgg]['quantite'] += $contenir->getContenirQuantite() ?? 0;
                }
            }
        }

        // 3. Créer la ListeCourse
        $listeCourse = new ListeCourse();
        $listeCourse->setListeLibelle('Liste de courses du ' . (new \DateTimeImmutable())->format('d/m/Y'))
                    ->setListeStatut('active')
                    ->setUser($user);

        $this->em->persist($listeCourse);

        // 4. Créer une ligne Contenir par ingrédient agrégé
        foreach ($agregat as $ligne) {
            $contenir = new Contenir();
            $contenir->setIngredient($ligne['ingredient'])
                     ->setContenirLibelleBrut($ligne['libelleBrut'])
                     ->setContenirQuantite($ligne['quantite'])
                     ->setContenirUnite($ligne['unite'])
                     ->setListeCourse($listeCourse);
            // recette = null : ces lignes appartiennent à la liste, pas à une recette
            $this->em->persist($contenir);
        }

        $this->em->flush();

        return $listeCourse;
    }
}