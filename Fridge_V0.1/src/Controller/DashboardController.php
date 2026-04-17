<?php

namespace App\Controller;

use App\Entity\Recette;
use App\Entity\User;
use App\Security\Voter\UserVoter;
use App\Repository\FavoriRepository;
use App\Repository\LikeRecetteRepository;
use App\Repository\RecetteRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard')]
#[IsGranted('ROLE_MODERATOR')]
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

    #[Route('/recipe/{id}/approve', name: 'app_admin_recipe_approve', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function recipeApprove(
        Recette                $objRecette,
        Request                $request,
        EntityManagerInterface $objEntityManager
    ): Response {
        if (!$this->isCsrfTokenValid('approve_recipe_' . $objRecette->getId(), $request->request->getString('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_dashboard');
        }

        $objRecette->setRecetteStatut('publie');
        $objEntityManager->flush();
        $this->addFlash('success', 'Recette validée.');

        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/recipe/{id}/reject', name: 'app_admin_recipe_reject', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function recipeReject(
        Recette                $objRecette,
        Request                $request,
        EntityManagerInterface $objEntityManager
    ): Response {
        if (!$this->isCsrfTokenValid('reject_recipe_' . $objRecette->getId(), $request->request->getString('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_dashboard');
        }

        $objRecette->setRecetteStatut('refuse');
        $objEntityManager->flush();
        $this->addFlash('success', 'Recette refusée.');

        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/user/{id}/role', name: 'app_admin_user_role', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted(UserVoter::EDIT_ROLE, subject: 'objUser')]
    public function userRole(
        User                   $objUser,
        Request                $request,
        EntityManagerInterface $objEntityManager
    ): Response {
        if (!$this->isCsrfTokenValid('admin_role_' . $objUser->getId(), $request->request->getString('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_dashboard');
        }

        $strRole = $request->request->getString('role', 'ROLE_USER');
        $objUser->setRoles($strRole === 'ROLE_USER' ? [] : [$strRole]);
        $objEntityManager->flush();
        $this->addFlash('success', 'Rôle mis à jour.');

        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/user/{id}/delete', name: 'app_admin_user_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted(UserVoter::DELETE, subject: 'objUser')]
    public function userDelete(
        User                   $objUser,
        Request                $request,
        EntityManagerInterface $objEntityManager
    ): Response {
        if (!$this->isCsrfTokenValid('delete_user_' . $objUser->getId(), $request->request->getString('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_dashboard');
        }

        $objEntityManager->remove($objUser);
        $objEntityManager->flush();
        $this->addFlash('success', 'Utilisateur supprimé.');

        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/user/{id}/edit', name: 'app_admin_user_edit', requirements: ['id' => '\d+'])]
    #[IsGranted(UserVoter::EDIT_PROFILE, subject: 'objUser')]
    public function userEdit(User $objUser): Response
    {
        // Stub : page d'édition à implémenter
        $this->addFlash('info', 'Fonctionnalité à venir.');
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/user/{id}/ban', name: 'app_admin_user_ban_confirm', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted(UserVoter::BAN, subject: 'objUser')]
    public function userBanConfirm(User $objUser, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('ban_user_' . $objUser->getId(), $request->request->getString('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_dashboard');
        }
        // Stub : logique de ban à implémenter
        $this->addFlash('info', 'Fonctionnalité à venir.');
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/comment/{id}/hide', name: 'app_admin_comment_hide', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function commentHide(int $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('hide_comment_' . $id, $request->request->getString('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_dashboard');
        }
        $this->addFlash('info', 'Fonctionnalité à venir.');
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/comment/{id}/delete', name: 'app_admin_comment_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function commentDelete(int $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete_comment_' . $id, $request->request->getString('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_dashboard');
        }
        $this->addFlash('info', 'Fonctionnalité à venir.');
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/report/{id}/dismiss', name: 'app_admin_report_dismiss', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_MODERATOR')]
    public function reportDismiss(int $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('dismiss_report_' . $id, $request->request->getString('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_dashboard');
        }
        $this->addFlash('info', 'Fonctionnalité à venir.');
        return $this->redirectToRoute('app_dashboard');
    }
}