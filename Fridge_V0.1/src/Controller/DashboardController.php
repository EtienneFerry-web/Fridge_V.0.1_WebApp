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

/**
 * Contrôleur du tableau de bord d'administration.
 *
 * Accessible uniquement aux modérateurs (ROLE_MODERATOR).
 * Permet de gérer les utilisateurs, consulter les statistiques globales et les dernières recettes publiées.
 *
 * Note : la modération de recettes a été désactivée. Toutes les recettes user sont créées
 * directement en statut 'prive' (visibles uniquement par leur créateur). Les routes
 * recipeApprove/recipeReject sont conservées au cas où la modération serait réactivée.
 */
#[Route('/dashboard')]
#[IsGranted('ROLE_MODERATOR')]
class DashboardController extends AbstractController
{
    /**
     * Affiche le tableau de bord avec les statistiques, les recettes récentes et la liste des utilisateurs.
     *
     * @param RecetteRepository     $objRecetteRepository Repository des recettes
     * @param LikeRecetteRepository $objLikeRepository    Repository des likes
     * @param FavoriRepository      $objFavoriRepository  Repository des favoris
     * @param UserRepository        $objUserRepository    Repository des utilisateurs
     * @param Request               $request              Requête HTTP (filtres utilisateurs via ?q= et ?role=)
     */
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
        // Système de modération désactivé : aucune recette n'est plus en 'en_attente'.
        // Toutes les recettes user sont créées directement en 'prive'.
        // Variable conservée pour compatibilité avec le template, au cas où la modération
        // serait réactivée plus tard.
        $arrPending = [];

        // --- Dernières recettes publiées (Spoonacular uniquement) ---
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

    /**
     * Approuve une recette en attente et la publie.
     *
     * Note : route conservée pour compatibilité, mais la modération est désactivée.
     *
     * @param Recette                $objRecette       La recette à approuver
     * @param Request                $request          Requête HTTP (contient le token CSRF)
     * @param EntityManagerInterface $objEntityManager Gestionnaire d'entités Doctrine
     */
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

    /**
     * Refuse une recette en attente.
     *
     * Note : route conservée pour compatibilité, mais la modération est désactivée.
     *
     * @param Recette                $objRecette       La recette à refuser
     * @param Request                $request          Requête HTTP (contient le token CSRF)
     * @param EntityManagerInterface $objEntityManager Gestionnaire d'entités Doctrine
     */
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

    /**
     * Met à jour le rôle d'un utilisateur (ex. ROLE_USER, ROLE_MODERATOR).
     *
     * @param User                   $objUser          L'utilisateur dont le rôle est modifié
     * @param Request                $request          Requête HTTP (contient le token CSRF et le nouveau rôle)
     * @param EntityManagerInterface $objEntityManager Gestionnaire d'entités Doctrine
     */
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

    /**
     * Supprime définitivement un compte utilisateur.
     *
     * @param User                   $objUser          L'utilisateur à supprimer
     * @param Request                $request          Requête HTTP (contient le token CSRF)
     * @param EntityManagerInterface $objEntityManager Gestionnaire d'entités Doctrine
     */
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

    /**
     * Page d'édition du profil d'un utilisateur par un administrateur (fonctionnalité à venir).
     *
     * @param User $objUser L'utilisateur à éditer
     */
    #[Route('/user/{id}/edit', name: 'app_admin_user_edit', requirements: ['id' => '\d+'])]
    #[IsGranted(UserVoter::EDIT_PROFILE, subject: 'objUser')]
    public function userEdit(User $objUser): Response
    {
        // Stub : page d'édition à implémenter
        $this->addFlash('info', 'Fonctionnalité à venir.');
        return $this->redirectToRoute('app_dashboard');
    }

    /**
     * Bannit un utilisateur (fonctionnalité à venir).
     *
     * @param User    $objUser L'utilisateur à bannir
     * @param Request $request Requête HTTP (contient le token CSRF)
     */
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

    /**
     * Masque un commentaire signalé (fonctionnalité à venir, réservée ROLE_ADMIN).
     *
     * @param int     $id      Identifiant du commentaire
     * @param Request $request Requête HTTP (contient le token CSRF)
     */
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

    /**
     * Supprime définitivement un commentaire (fonctionnalité à venir, réservée ROLE_ADMIN).
     *
     * @param int     $id      Identifiant du commentaire
     * @param Request $request Requête HTTP (contient le token CSRF)
     */
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

    /**
     * Ignore un signalement (fonctionnalité à venir).
     *
     * @param int     $id      Identifiant du signalement
     * @param Request $request Requête HTTP (contient le token CSRF)
     */
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