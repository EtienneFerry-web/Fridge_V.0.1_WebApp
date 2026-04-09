<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function index(Request $request): Response
    {
        $query = $request->query->get('q', '');

        $recipes = [];

        return $this->render('search/index.html.twig', [
            'recipes' => $recipes,
            'query'   => $query,
        ]);
    }
}