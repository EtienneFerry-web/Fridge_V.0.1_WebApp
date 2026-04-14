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

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        RecetteRepository     $objRecetteRepository,
        LikeRecetteRepository $objLikeRecetteRepository,
        FavoriRepository      $objFavoriRepository,
        PaginatorInterface    $paginator,
        Request               $request
    ): Response {
        $arrRecettesCarousel = $objRecetteRepository->findBy([], ['id' => 'DESC'], 6);

        $queryRecettes = $objRecetteRepository->createQueryBuilder('r')
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