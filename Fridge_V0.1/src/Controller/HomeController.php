<?php

namespace App\Controller;

use App\Repository\FavoriRepository;
use App\Repository\LikeRecetteRepository;
use App\Repository\RecetteRepository;
use App\Service\TheMealDbClient;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur de la page d'accueil.
 *
 * Carrousel : recettes BDD (publiées par les utilisateurs).
 * Catalogue "Toutes les pépites" : 24 recettes TheMealDB, paginées 8 par 8.
 */
final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        RecetteRepository     $objRecetteRepository,
        LikeRecetteRepository $objLikeRecetteRepository,
        FavoriRepository      $objFavoriRepository,
        TheMealDbClient       $mealDbClient,
        PaginatorInterface    $paginator,
        Request               $request
    ): Response {
        // Carrousel : 6 dernières recettes BDD publiées
        $arrRecettesCarousel = $objRecetteRepository->findBy(
            ['recetteStatut' => 'publie'],
            ['id' => 'DESC'],
            6
        );

        // Likes / favoris pour le carrousel
        $arrLikedIds   = [];
        $arrFavoriIds  = [];
        $arrLikeCounts = [];
        $objUser = $this->getUser();

        if ($objUser) {
            $arrLikedIds  = $objLikeRecetteRepository->findLikedIdsByUser($objUser);
            $arrFavoriIds = $objFavoriRepository->findFavoriIdsByUser($objUser);
        }

        foreach ($arrRecettesCarousel as $objRecette) {
            $arrLikeCounts[$objRecette->getId()] = $objLikeRecetteRepository->count([
                'likeRecette' => $objRecette,
            ]);
        }

        // Catalogue : 24 recettes TheMealDB paginées 8 par 8
        $arrAllMeals = [];
        try {
            $arrAllMeals = $mealDbClient->getDiscoveryMeals(24);
        } catch (\Throwable) {
            // Silencieux si l'API est indisponible
        }

        $arrRecettes = $paginator->paginate(
            $arrAllMeals,
            $request->query->getInt('page', 1),
            8
        );

        return $this->render('home/index.html.twig', [
            'arrRecettesCarousel' => $arrRecettesCarousel,
            'arrRecettes'         => $arrRecettes,
            'arrLikedIds'         => $arrLikedIds,
            'arrLikeCounts'       => $arrLikeCounts,
            'arrFavoriIds'        => $arrFavoriIds,
            'activeRegime'        => 'all',
            'activeSort'          => 'recent',
        ]);
    }
}
