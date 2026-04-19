<?php

namespace App\Controller;

use App\Repository\IngredientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur de gestion des ingrédients.
 *
 * Fournit la page de liste des ingrédients ainsi qu'un endpoint de recherche autocomplète en JSON.
 */
final class IngredientController extends AbstractController
{
    /**
     * Affiche la page principale des ingrédients.
     */
    #[Route('/ingredient', name: 'app_ingredient')]
    public function index(): Response
    {
        return $this->render('ingredient/index.html.twig', [
            'controller_name' => 'IngredientController',
        ]);
    }

    /**
     * Recherche des ingrédients par libellé (autocomplète).
     *
     * Retourne une liste JSON vide si la requête fait moins de 2 caractères.
     *
     * @param Request               $request              Requête HTTP (paramètre ?q=)
     * @param IngredientRepository  $ingredientRepository Repository des ingrédients
     */
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
