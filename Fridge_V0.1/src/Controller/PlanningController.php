<?php

namespace App\Controller;

use App\Entity\Planning;
use App\Repository\FavoriRepository;
use App\Repository\LikeRecetteRepository;
use App\Repository\PlanningRepository;
use App\Repository\RecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur de gestion du planning hebdomadaire de repas.
 *
 * Permet à l'utilisateur connecté de visualiser, ajouter, supprimer et vider
 * les recettes planifiées sur une grille jour × moment de la journée.
 */
#[Route('/planning')]
final class PlanningController extends AbstractController
{
    const JOURS   = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
    const MOMENTS = ['petit_dejeuner', 'dejeuner', 'diner', 'dessert'];
    const MOMENTS_LABELS = [
        'petit_dejeuner' => 'Petit-déjeuner',
        'dejeuner'       => 'Déjeuner',
        'diner'          => 'Dîner',
        'dessert'        => 'Dessert',
    ];

    /**
     * Affiche la grille du planning hebdomadaire avec les recettes likées et favorites de l'utilisateur.
     *
     * @param PlanningRepository    $objPlanningRepository Repository du planning
     * @param LikeRecetteRepository $objLikeRepository     Repository des likes
     * @param FavoriRepository      $objFavoriRepository   Repository des favoris
     */
    #[Route('', name: 'app_planning')]
    public function index(
        PlanningRepository    $objPlanningRepository,
        LikeRecetteRepository $objLikeRepository,
        FavoriRepository      $objFavoriRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $objUser = $this->getUser();

        $arrPlannings = $objPlanningRepository->findBy(['planningUser' => $objUser]);

        $arrGrille = [];
        foreach (self::JOURS as $strJour) {
            foreach (self::MOMENTS as $strMoment) {
                $arrGrille[$strJour][$strMoment] = null;
            }
        }
        foreach ($arrPlannings as $objPlanning) {
            $arrGrille[$objPlanning->getPlanningJour()][$objPlanning->getPlanningMoment()] = $objPlanning;
        }

        $arrLikedRecettes  = $objLikeRepository->findLikedRecettesByUser($objUser);
        $arrFavoriRecettes = $objFavoriRepository->findFavoriRecettesByUser($objUser);

        return $this->render('planning/index.html.twig', [
            'arrGrille'         => $arrGrille,
            'arrJours'          => self::JOURS,
            'arrMoments'        => self::MOMENTS,
            'arrMomentsLabels'  => self::MOMENTS_LABELS,
            'arrLikedRecettes'  => $arrLikedRecettes,
            'arrFavoriRecettes' => $arrFavoriRecettes,
        ]);
    }

    /**
     * Ajoute une recette dans le planning à un jour et un moment donnés.
     *
     * Si un créneau existait déjà, il est remplacé. Retourne une réponse JSON avec les données de la recette planifiée.
     *
     * @param Request                $request               Requête HTTP (jour, moment, recette_id)
     * @param RecetteRepository      $objRecetteRepository  Repository des recettes
     * @param PlanningRepository     $objPlanningRepository Repository du planning
     * @param EntityManagerInterface $objEntityManager      Gestionnaire d'entités Doctrine
     */
    #[Route('/ajouter', name: 'app_planning_add', methods: ['POST'])]
    public function add(
        Request                $request,
        RecetteRepository      $objRecetteRepository,
        PlanningRepository     $objPlanningRepository,
        EntityManagerInterface $objEntityManager
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $objUser = $this->getUser();

        $strJour      = $request->request->get('jour');
        $strMoment    = $request->request->get('moment');
        $intRecetteId = (int) $request->request->get('recette_id');

        if (!in_array($strJour, self::JOURS) || !in_array($strMoment, self::MOMENTS)) {
            return new JsonResponse(['error' => 'Données invalides.'], 400);
        }

        $objRecette = $objRecetteRepository->find($intRecetteId);
        if (!$objRecette) {
            return new JsonResponse(['error' => 'Recette introuvable.'], 404);
        }

        $objExistant = $objPlanningRepository->findOneBy([
            'planningUser'   => $objUser,
            'planningJour'   => $strJour,
            'planningMoment' => $strMoment,
        ]);
        if ($objExistant) {
            $objEntityManager->remove($objExistant);
        }

        $objPlanning = new Planning();
        $objPlanning->setPlanningUser($objUser)
                    ->setPlanningJour($strJour)
                    ->setPlanningMoment($strMoment)
                    ->setPlanningRecette($objRecette);

        $objEntityManager->persist($objPlanning);
        $objEntityManager->flush();

        return new JsonResponse([
            'success'    => true,
            'id'         => $objPlanning->getId(),
            'titre'      => $objRecette->getRecetteLibelle(),
            'photo'      => $objRecette->getRecettePhoto(),
            'temps'      => $objRecette->getRecetteTempsPrepa() + $objRecette->getRecetteTempsCuisson(),
            'recette_id' => $objRecette->getId(),
        ]);
    }

    /**
     * Supprime une entrée du planning appartenant à l'utilisateur connecté.
     *
     * @param Planning               $objPlanning      L'entrée de planning à supprimer
     * @param EntityManagerInterface $objEntityManager Gestionnaire d'entités Doctrine
     */
    #[Route('/supprimer/{id}', name: 'app_planning_delete', methods: ['POST'])]
    public function delete(
        Planning               $objPlanning,
        EntityManagerInterface $objEntityManager
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($objPlanning->getPlanningUser() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Accès refusé.'], 403);
        }

        $objEntityManager->remove($objPlanning);
        $objEntityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    /**
     * Vide intégralement le planning de l'utilisateur connecté.
     *
     * @param PlanningRepository     $objPlanningRepository Repository du planning
     * @param EntityManagerInterface $objEntityManager      Gestionnaire d'entités Doctrine
     */
    #[Route('/vider', name: 'app_planning_clear', methods: ['POST'])]
    public function clear(
        PlanningRepository     $objPlanningRepository,
        EntityManagerInterface $objEntityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        foreach ($objPlanningRepository->findBy(['planningUser' => $this->getUser()]) as $objPlanning) {
            $objEntityManager->remove($objPlanning);
        }
        $objEntityManager->flush();

        $this->addFlash('success', 'Planning vidé.');
        return $this->redirectToRoute('app_planning');
    }

    /**
     * Déplace une entrée du planning vers un nouveau jour et moment.
     *
     * Si la case cible est déjà occupée, l'ancienne recette est remplacée (supprimée).
     * Appelée en AJAX par le drag & drop du tableau.
     *
     * @param Request                $request               Requête HTTP (planning_id, nouveau_jour, nouveau_moment)
     * @param PlanningRepository     $objPlanningRepository Repository du planning
     * @param EntityManagerInterface $objEntityManager      Gestionnaire d'entités Doctrine
     */
    #[Route('/deplacer', name: 'app_planning_move', methods: ['POST'])]
    public function move(
        Request                $request,
        PlanningRepository     $objPlanningRepository,
        EntityManagerInterface $objEntityManager
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $objUser = $this->getUser();

        $intPlanningId  = (int) $request->request->get('planning_id');
        $strNouveauJour = $request->request->get('nouveau_jour');
        $strNouveauMoment = $request->request->get('nouveau_moment');

        // Validation
        if (!in_array($strNouveauJour, self::JOURS) || !in_array($strNouveauMoment, self::MOMENTS)) {
            return new JsonResponse(['error' => 'Données invalides.'], 400);
        }

        // Récupérer l'entrée à déplacer
        $objPlanning = $objPlanningRepository->find($intPlanningId);
        if (!$objPlanning || $objPlanning->getPlanningUser() !== $objUser) {
            return new JsonResponse(['error' => 'Planning introuvable ou accès refusé.'], 403);
        }

        // Si la case cible est déjà occupée → supprimer l'existante (remplacement)
        $objExistant = $objPlanningRepository->findOneBy([
            'planningUser'   => $objUser,
            'planningJour'   => $strNouveauJour,
            'planningMoment' => $strNouveauMoment,
        ]);
        if ($objExistant && $objExistant->getId() !== $objPlanning->getId()) {
            $objEntityManager->remove($objExistant);
        }

        // Déplacer
        $objPlanning->setPlanningJour($strNouveauJour)
                    ->setPlanningMoment($strNouveauMoment);

        $objEntityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}
