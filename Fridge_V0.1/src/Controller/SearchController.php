<?php

namespace App\Controller;

use App\Repository\RecetteRepository;
use App\Repository\RegimeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search', methods: ['GET'])]
    public function index(
        Request $request,
        RecetteRepository $recetteRepository,
        RegimeRepository $regimeRepository
    ): Response {
        $query = $request->query->get('q', '');
        $arrDifficulte          = $request->query->all('difficulte');
        $arrRegimes             = $request->query->all('regimes');
        $strOrigine             = $request->query->get('origine', '');
        $intTempsPreparationMax = $request->query->getInt('temps_preparation_max', 120);
        $strSortBy              = $request->query->get('sort_by', 'pertinence');

        $recette                = $recetteRepository->findBySearch(
            $query,
            $arrDifficulte,
            $arrRegimes,
            $strOrigine,
            $intTempsPreparationMax,
            $strSortBy
        );

        return $this->render('search/index.html.twig', [
            'recipes' => $recette,
            'query'   => $query,
            'difficulte' => $arrDifficulte,
            'regimes' => $arrRegimes,
            'origine' => $strOrigine,
            'temps_preparation_max' => $intTempsPreparationMax,
            'sort_by' => $strSortBy,
        ]);
    }
}