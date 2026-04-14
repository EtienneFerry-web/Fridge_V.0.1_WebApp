<?php

namespace App\Controller;

use App\Repository\FavoriRepository;
use App\Repository\LikeRecetteRepository;
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
        RegimeRepository $regimeRepository,
        LikeRecetteRepository $objLikeRecetteRepository,
        FavoriRepository      $objFavoriRepository
    ): Response {
        $query = $request->query->get('q', '');
        $arrDifficulte          = $request->query->all('difficulte');
        $arrRegimes             = $request->query->all('regimes');
        $strOrigine             = $request->query->get('origine', '');
        $intTempsPreparationMax = $request->query->getInt('temps_preparation_max', 120);
        $strSortBy              = $request->query->get('sort_by', 'pertinence');

        $arrRecettes                = $recetteRepository->findBySearch(
            $query,
            $arrDifficulte,
            $arrRegimes,
            $strOrigine,
            $intTempsPreparationMax,
            $strSortBy
        );

        // Likes
        $arrLikedIds   = [];
        $arrLikeCounts = [];
        $arrFavoriIds = [];
        $objUser       = $this->getUser();

        if ($objUser) {
            $arrLikedIds = $objLikeRecetteRepository->findLikedIdsByUser($objUser);
            $arrFavoriIds = $objFavoriRepository->findFavoriIdsByUser($objUser);
        }

        foreach ($arrRecettes as $objRecette) {
            $arrLikeCounts[$objRecette->getId()] = $objLikeRecetteRepository->count([
                'likeRecette' => $objRecette
            ]);
        }

        return $this->render('search/index.html.twig', [
            'recipes'               => $arrRecettes,
            'query'                 => $query,
            'difficulte'            => $arrDifficulte,
            'regimes'               => $regimeRepository->findAll(),
            'origine'               => $strOrigine,
            'temps_preparation_max' => $intTempsPreparationMax,
            'sort_by'               => $strSortBy,
            'arrLikedIds'           => $arrLikedIds,
            'arrLikeCounts'         => $arrLikeCounts,
            'arrFavoriIds'          => $arrFavoriIds,
        ]);
    }
}