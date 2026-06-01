<?php

namespace App\Controller;

use App\Repository\RecetteRepository;
use App\Repository\StockerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class StockSuggestionsController extends AbstractController
{
    #[Route('/stock/suggestions', name: 'app_stock_suggestions', methods: ['GET'])]
    public function index(
        StockerRepository $objStockerRepository,
        RecetteRepository $objRecetteRepository
    ): Response {
        $arrStock = $objStockerRepository->findBy(['user' => $this->getUser()]);

        $arrIngredientIds = [];
        foreach ($arrStock as $objStocker) {
            if ($objStocker->getIngredient() !== null) {
                $arrIngredientIds[] = $objStocker->getIngredient()->getId();
            }
        }

        $arrStockIngredientIds = array_flip($arrIngredientIds);

        $arrRecettes = $objRecetteRepository->findByIngredients($arrIngredientIds);

        $arrSuggestions = [];
        foreach ($arrRecettes as $objRecette) {
            $intTotal    = 0;
            $intMatch    = 0;
            $arrMissing  = [];

            foreach ($objRecette->getContenirs() as $objContenir) {
                if ($objContenir->getIngredient() === null) {
                    continue;
                }

                $intTotal++;

                if (isset($arrStockIngredientIds[$objContenir->getIngredient()->getId()])) {
                    $intMatch++;
                } else {
                    $arrMissing[] = [
                        'nom'     => $objContenir->getIngredient()->getIngredientLibelle(),
                        'quantite' => $objContenir->getContenirQuantite(),
                        'unite'   => $objContenir->getContenirUnite(),
                    ];
                }
            }

            if ($intTotal === 0) {
                continue;
            }

            $arrSuggestions[] = [
                'recette'           => $objRecette,
                'matchCount'        => $intMatch,
                'totalIngredients'  => $intTotal,
                'matchPercent'      => (int) round(($intMatch / $intTotal) * 100),
                'missingIngredients' => $arrMissing,
            ];
        }

        usort($arrSuggestions, fn($a, $b) => $b['matchPercent'] <=> $a['matchPercent']);

        return $this->render('stock/suggestions.html.twig', [
            'arrSuggestions' => $arrSuggestions,
            'stockIsEmpty'   => empty($arrIngredientIds),
        ]);
    }
}
