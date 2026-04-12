<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard')]
class DashboardController extends AbstractController
{
    #[Route('', name: 'app_dashboard')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');

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

    // --- Stub routes referenced by dashboard/index.html.twig ---
    // These will be replaced by real implementations when the features are built.

    #[Route('/recipe/new', name: 'app_admin_recipe_new')]
    public function recipeNew(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->addFlash('info', 'Fonctionnalité à venir.');
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/recipe/{id}/approve', name: 'app_admin_recipe_approve')]
    public function recipeApprove(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->addFlash('info', 'Fonctionnalité à venir.');
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/recipe/{id}/reject', name: 'app_admin_recipe_reject')]
    public function recipeReject(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->addFlash('info', 'Fonctionnalité à venir.');
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/user/{id}/role', name: 'app_admin_user_role', methods: ['POST'])]
    public function userRole(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->addFlash('info', 'Fonctionnalité à venir.');
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

    #[Route('/user/{id}/delete', name: 'app_admin_user_delete')]
    public function userDelete(int $id): Response
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
