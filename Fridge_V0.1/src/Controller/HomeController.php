<?php

namespace App\Controller;

use App\Repository\RecetteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(RecetteRepository $objRecetteRepository): Response
    {
        $arrRecettesCarousel = $objRecetteRepository->findBy([], ['id' => 'DESC'], 6);
        $arrRecettes         = $objRecetteRepository->findAll();

        return $this->render('home/index.html.twig', [
            'arrRecettesCarousel' => $arrRecettesCarousel,
            'arrRecettes'         => $arrRecettes,
        ]);
    }
}