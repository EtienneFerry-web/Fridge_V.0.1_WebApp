<?php

namespace App\Controller;

use App\Repository\IngredientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class IngredientController extends AbstractController
{
    #[Route('/ingredient', name: 'app_ingredient')]
    public function index(): Response
    {
        return $this->render('ingredient/index.html.twig', [
            'controller_name' => 'IngredientController',
        ]);
    }

    #[Route('/ingredient/search', name: 'app_ingredient_search', methods: ['GET'])]
    public function search(Request $request, IngredientRepository $ingredientRepository): JsonResponse
    {
        $query = $request->query->get('q', '');

        if (strlen($query) < 2) {
            return $this->json([]);
        }

        $ingredients = $ingredientRepository->createQueryBuilder('i')
            ->where('LOWER(i.ingredientLibelle) LIKE LOWER(:query)')
            ->setParameter('query', "%$query%")
            ->getQuery()
            ->getResult()
        ;

        return $this->json(array_map(
            fn($ingredient) => [
            'id' => $ingredient->getId(),
            'libelle' => $ingredient->getIngredientLibelle(),
            ], 
            $ingredients
        ));
    }
}
