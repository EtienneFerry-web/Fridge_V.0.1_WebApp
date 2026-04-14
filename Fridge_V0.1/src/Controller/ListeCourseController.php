<?php

namespace App\Controller;

use App\Repository\ListeCourseRepository;
use App\Service\ListeCourseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/liste-courses')]
#[IsGranted('ROLE_USER')]
class ListeCourseController extends AbstractController
{
    #[Route('/', name: 'app_liste_course_index')]
    public function index(ListeCourseRepository $listeCourseRepository): Response
    {
        $user  = $this->getUser();
        $listes = $listeCourseRepository->findBy(
            ['user' => $user],
            ['listeDateCreation' => 'DESC']
        );

        return $this->render('liste_course/index.html.twig', [
            'listes' => $listes,
        ]);
    }

    #[Route('/generer', name: 'app_liste_course_generer', methods: ['POST'])]
    public function generer(ListeCourseService $listeCourseService): Response
    {
        $user = $this->getUser();
        $liste = $listeCourseService->genererDepuisPlanning($user);

        $this->addFlash('success', 'Votre liste de courses a été générée avec succès !');

        return $this->redirectToRoute('app_liste_course_show', ['id' => $liste->getId()]);
    }

    #[Route('/{id}', name: 'app_liste_course_show')]
    public function show(int $id, ListeCourseRepository $listeCourseRepository): Response
    {
        $user  = $this->getUser();
        $liste = $listeCourseRepository->find($id);

        if (!$liste || $liste->getUser() !== $user) {
            throw $this->createNotFoundException('Liste introuvable.');
        }

        // Regrouper les ingrédients par type pour l'affichage
        $parCategorie = [];
        foreach ($liste->getContenirs() as $contenir) {
            $ingredient = $contenir->getIngredient();
            $categorie  = $ingredient?->getIngredientType() ?? 'Autre';
            $parCategorie[$categorie][] = $contenir;
        }
        ksort($parCategorie);

        return $this->render('liste_course/show.html.twig', [
            'liste'        => $liste,
            'parCategorie' => $parCategorie,
        ]);
    }
}