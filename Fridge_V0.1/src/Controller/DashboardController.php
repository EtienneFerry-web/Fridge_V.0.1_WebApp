<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('dashboard/index.html.twig', [
            'stats'           => [
                'totalLikes'    => 0,
                'totalRecipes'  => 0,
                'totalComments' => 0,
                'reportsCount'  => 0,
            ],
            'topLikedRecipes' => [],
            'latestRecipes'   => [],
            'users'           => [],
            'pendingRecipes'  => [],
            'reports'         => [],
        ]);
    }
}