<?php

namespace App\Controller;

use App\Entity\Recette;
use App\Repository\FavoriRepository;
use App\Repository\LikeRecetteRepository;
use App\Repository\RecetteRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard')]
class DashboardController extends AbstractController
{
    #[Route('', name: 'app_dashboard')]
    public function index(
        RecetteRepository     $objRecetteRepository,
        LikeRecetteRepository $objLikeRepository,
        FavoriRepository      $objFavoriRepository,
        UserRepository        $objUserRepository,
        Request               $request
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');

        // --- Stats ---
        $intTotalLikes   = $objLikeRepository->count([]);
        $intTotalFavoris = $objFavoriRepository->count([]);
        $intTotalRecipes = $objRecetteRepository->count(['recetteStatut' => 'publie']);

        // --- Top recettes likées ---
        $arrTopLiked = $objLikeRepository->findTopLikedRecettes(5);

        // --- Recettes en attente ---
        $arrPending = $objRecetteRepository->findBy(
            ['recetteStatut' => 'en_attente'],
            ['recetteCreatedAt' => 'DESC']
        );

        // --- Dernières recettes publiées ---
        $arrLatest = $objRecetteRepository->findBy(
            ['recetteStatut' => 'publie'],
            ['recetteCreatedAt' => 'DESC'],
            5
        );

        // --- Utilisateurs avec filtre ---
        $strQuery = $request->query->get('q', '');
        $strRole  = $request->query->get('role', 'all');
        $arrUsers = $objUserRepository->findByFilter($strQuery, $strRole);

        return $this->render('dashboard/index.html.twig', [
            'stats' => [
                'totalLikes'    => $intTotalLikes,
                'totalRecipes'  => $intTotalRecipes,
                'totalFavoris'  => $intTotalFavoris,
                'reportsCount'  => 0,
            ],
            'topLikedRecipes' => $arrTopLiked,
            'latestRecipes'   => $arrLatest,
            'pendingRecipes'  => $arrPending,
            'users'           => $arrUsers,
            'reports'         => [],
        ]);
    }

    #[Route('/recipe/{id}/approve', name: 'app_admin_recipe_approve')]
    public function recipeApprove(
        Recette                $objRecette,
        EntityManagerInterface $objEntityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');
        $objRecette->setRecetteStatut('publie');
        $objEntityManager->flush();
        $this->addFlash('success', 'Recette validée.');
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/recipe/{id}/reject', name: 'app_admin_recipe_reject')]
    public function recipeReject(
        Recette                $objRecette,
        EntityManagerInterface $objEntityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');
        $objRecette->setRecetteStatut('refuse');
        $objEntityManager->flush();
        $this->addFlash('success', 'Recette refusée.');
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/recipe/new', name: 'app_admin_recipe_new')]
    public function recipeNew(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->addFlash('info', 'Fonctionnalité à venir.');
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/user/{id}/role', name: 'app_admin_user_role', methods: ['POST'])]
    public function userRole(
        int                    $id,
        Request                $request,
        UserRepository         $objUserRepository,
        EntityManagerInterface $objEntityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $objUser = $objUserRepository->find($id);
        if ($objUser) {
            $strRole = $request->request->get('role', 'ROLE_USER');
            $objUser->setRoles($strRole === 'ROLE_USER' ? [] : [$strRole]);
            $objEntityManager->flush();
            $this->addFlash('success', 'Rôle mis à jour.');
        }
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/user/{id}/delete', name: 'app_admin_user_delete')]
    public function userDelete(
        int                    $id,
        UserRepository         $objUserRepository,
        EntityManagerInterface $objEntityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $objUser = $objUserRepository->find($id);
        if ($objUser) {
            $objEntityManager->remove($objUser);
            $objEntityManager->flush();
            $this->addFlash('success', 'Utilisateur supprimé.');
        }
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/user/{id}/edit', name: 'app_admin_user_edit')]
    public function userEdit(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->addFlash('info', 'Fonctionnalité à venir.');
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/user/{id}/ban', name: 'app_admin_user_ban')]
    public function userBan(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->addFlash('info', 'Fonctionnalité à venir.');
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/user/ban/confirm', name: 'app_admin_user_ban_confirm', methods: ['POST'])]
    public function userBanConfirm(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->addFlash('info', 'Fonctionnalité à venir.');
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/comment/{id}/hide', name: 'app_admin_comment_hide')]
    public function commentHide(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->addFlash('info', 'Fonctionnalité à venir.');
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/comment/{id}/delete', name: 'app_admin_comment_delete')]
    public function commentDelete(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->addFlash('info', 'Fonctionnalité à venir.');
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/report/{id}/dismiss', name: 'app_admin_report_dismiss')]
    public function reportDismiss(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->addFlash('info', 'Fonctionnalité à venir.');
        return $this->redirectToRoute('app_dashboard');
    }
}