<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RecipeController extends AbstractController
{
    #[Route('/recipe', name: 'app_recipe')]
    public function index(): Response
    {
        return $this->render('recipe/index.html.twig', [
            'controller_name' => 'RecipeController',
        ]);
    }

    #[Route('/recipe/{id}', name: 'app_recipe_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        // Stub — will be implemented with the Recipe feature
        return $this->render('recipe/index.html.twig', [
            'controller_name' => 'RecipeController',
        ]);
    }
}
