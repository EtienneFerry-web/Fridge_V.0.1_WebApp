<?php

namespace App\Controller;

use App\Repository\FavoriRepository;
use App\Repository\LikeRecetteRepository;
use App\Repository\RecetteRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur de la page d'accueil.
 *
 * Affiche le carrousel des dernières recettes et la liste paginée de toutes les recettes publiées.
 */
final class HomeController extends AbstractController
{
    /**
     * Affiche la page d'accueil avec un carrousel et une liste paginée de recettes.
     *
     * @param RecetteRepository     $objRecetteRepository     Repository des recettes
     * @param LikeRecetteRepository $objLikeRecetteRepository Repository des likes
     * @param FavoriRepository      $objFavoriRepository      Repository des favoris
     * @param PaginatorInterface    $paginator                Service de pagination KnpPaginator
     * @param Request               $request                  Requête HTTP (paramètre ?page=)
     */
    #[Route('/', name: 'app_home')]
    public function index(
        RecetteRepository     $objRecetteRepository,
        LikeRecetteRepository $objLikeRecetteRepository,
        FavoriRepository      $objFavoriRepository,
        PaginatorInterface    $paginator,
        Request               $request
    ): Response {
        $arrRecettesCarousel = $objRecetteRepository->findBy(['recetteStatut' => 'publie'], ['id' => 'DESC'], 6);

        $queryRecettes = $objRecetteRepository->createQueryBuilder('r')
            ->where('r.recetteStatut = :statut')
            ->setParameter('statut', 'publie')
            ->orderBy('r.recetteCreatedAt', 'DESC')
            ->getQuery();

        $arrRecettes = $paginator->paginate(
            $queryRecettes,
            $request->query->getInt('page', 1),
            8
        );

        $arrLikedIds   = [];
        $arrLikeCounts = [];
        $arrFavoriIds = [];
        $objUser       = $this->getUser();

        if ($objUser) {
            $arrLikedIds = $objLikeRecetteRepository->findLikedIdsByUser($objUser);
            $arrFavoriIds = $objFavoriRepository->findFavoriIdsByUser($objUser);
        }

        $arrAllRecettes = array_merge(
            iterator_to_array($arrRecettes),
            $arrRecettesCarousel
        );

        foreach ($arrAllRecettes as $objRecette) {
            $arrLikeCounts[$objRecette->getId()] = $objLikeRecetteRepository->count([
                'likeRecette' => $objRecette
            ]);
        }

        return $this->render('home/index.html.twig', [
            'arrRecettesCarousel' => $arrRecettesCarousel,
            'arrRecettes'         => $arrRecettes,
            'arrLikedIds'         => $arrLikedIds,
            'arrLikeCounts'       => $arrLikeCounts,
            'arrFavoriIds'        => $arrFavoriIds,
        ]);
    }
}