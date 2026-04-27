<?php

namespace App\Controller;

use App\Entity\ListeCourse;
use App\Repository\ContenirRepository;
use App\Repository\ListeCourseRepository;
use App\Service\ListeCourseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Contrôleur de gestion des listes de courses.
 *
 * Accessible uniquement aux utilisateurs connectés (ROLE_USER).
 * Permet de consulter, générer, cocher et supprimer des listes de courses basées sur le planning.
 */
#[Route('/liste-courses')]
#[IsGranted('ROLE_USER')]
class ListeCourseController extends AbstractController
{
    /**
     * Affiche toutes les listes de courses de l'utilisateur connecté, triées par date de création.
     *
     * @param ListeCourseRepository $listeCourseRepository Repository des listes de courses
     */
    #[Route('/', name: 'app_liste_course_index')]
    public function index(ListeCourseRepository $listeCourseRepository): Response
    {
        $user  = $this->getUser();
        $listes = $listeCourseRepository->findBy(
            ['user' => $user],
            ['listeDateCreation' => 'DESC']
        );

        return $this->render('courses/index.html.twig', [
            'listes' => $listes,
        ]);
    }

    /**
     * Génère automatiquement une liste de courses à partir du planning hebdomadaire de l'utilisateur.
     *
     * @param ListeCourseService $listeCourseService Service de génération de liste de courses
     */
    #[Route('/generer', name: 'app_liste_course_generer', methods: ['POST'])]
    public function generer(ListeCourseService $listeCourseService): Response
    {
        $user = $this->getUser();
        $liste = $listeCourseService->genererDepuisPlanning($user);

        if (!$liste) {
            $this->addFlash('warning', 'Aucune recette dans votre planning pour générer une liste.');
            return $this->redirectToRoute('app_liste_course_index');
        }

        $this->addFlash('success', 'Votre liste de courses a été générée avec succès !');

        return $this->redirectToRoute('app_liste_course_index', ['id' => $liste->getId()]);
    }

    /**
     * Coche ou décoche un ingrédient dans une liste de courses.
     *
     * Retourne une réponse JSON avec le résultat et le nouvel état de la case à cocher.
     *
     * @param int                    $id                Identifiant de l'entrée Contenir
     * @param ContenirRepository     $contenirRepository Repository des lignes de liste
     * @param EntityManagerInterface $em                Gestionnaire d'entités Doctrine
     */
    #[Route('/check/{id}', name: 'app_liste_course_check', methods: ['POST'])]
    public function toggleCheck(int $id, ContenirRepository $contenirRepository, EntityManagerInterface $em): JsonResponse
    {
        $contenir = $contenirRepository->find($id);
        if (!$contenir) {
            return new JsonResponse(['success' => false], 404);
        }

        // Vérifier que la liste appartient à l'utilisateur
        if ($contenir->getListeCourse()->getUser() !== $this->getUser()) {
            return new JsonResponse(['success' => false], 403);
        }

        $contenir->setContenirEstCoche(!$contenir->isContenirEstCoche());
        $em->flush();

        return new JsonResponse(['success' => true, 'isCoche' => $contenir->isContenirEstCoche()]);
    }

    /**
     * Supprime une liste de courses appartenant à l'utilisateur connecté.
     *
     * @param ListeCourse            $liste La liste à supprimer
     * @param EntityManagerInterface $em    Gestionnaire d'entités Doctrine
     */
    #[Route('/supprimer/{id}', name: 'app_liste_course_delete', methods: ['POST'])]
    public function delete(ListeCourse $liste, EntityManagerInterface $em): Response
    {
        if ($liste->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($liste);
        $em->flush();

        $this->addFlash('success', 'La liste de courses a été supprimée.');

        return $this->redirectToRoute('app_liste_course_index');
    }

    /**
     * Affiche le détail d'une liste de courses, regroupé par catégorie d'ingrédients.
     *
     * @param int                   $id                   Identifiant de la liste
     * @param ListeCourseRepository $listeCourseRepository Repository des listes de courses
     */
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
            if ($ingredient !== null) {
                $categorie = $ingredient->getIngredientType() ?? 'Autre';
            } else {
                // Ingrédients Spoonacular : pas de catégorie connue
                $categorie = 'Spoonacular';
            }
            $parCategorie[$categorie][] = $contenir;
        }
        ksort($parCategorie);

        return $this->render('courses/show.html.twig', [
            'liste'        => $liste,
            'parCategorie' => $parCategorie,
        ]);
    }
}