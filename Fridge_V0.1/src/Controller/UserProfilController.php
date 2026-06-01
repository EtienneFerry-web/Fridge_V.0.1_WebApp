<?php

namespace App\Controller;

use App\Entity\LikeRecette;
use App\Entity\User;
use App\Repository\LikeRecetteRepository;
use App\Repository\ListeRepository;
use App\Repository\RecetteRepository;
use App\Repository\UserRepository;
use App\Form\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Contrôleur du profil utilisateur.
 *
 * Accessible uniquement aux utilisateurs connectés (ROLE_USER).
 * Permet de consulter, modifier et supprimer son compte.
 */
#[IsGranted('ROLE_USER')]
final class UserProfilController extends AbstractController
{
    /**
     * Affiche le profil de l'utilisateur connecté avec ses recettes likées et ses listes.
     */
    #[Route('/user/profil', name: 'app_user_profil')]
    public function index(
        RecetteRepository $objRecetteRepo,
        ListeRepository   $listeRepository
    ): Response {
        $objUser = $this->getUser();

        $arrLikes  = $objRecetteRepo->findLikedByUserWithCount($objUser);
        $arrListes = $listeRepository->findBy(['user' => $objUser], ['id' => 'DESC']);

        return $this->render('user/profil.html.twig', [
            'arrLikes'  => $arrLikes,
            'arrListes' => $arrListes,
        ]);
    }

    /**
     * Affiche et traite le formulaire de modification du profil (informations et mot de passe).
     *
     * Le mot de passe n'est re-haché et mis à jour que s'il est fourni.
     *
     * @param Request                     $objRequest       Requête HTTP
     * @param EntityManagerInterface      $objEntityManager Gestionnaire d'entités Doctrine
     * @param UserPasswordHasherInterface $objPasswordHasher Service de hachage de mot de passe
     */
    #[Route('/user/profil/edit', name: 'app_edit_user_profil')]
    public function edit(
        Request $objRequest,
        EntityManagerInterface $objEntityManager,
        UserPasswordHasherInterface $objPasswordHasher
    ): Response {
        $objUser = $this->getUser();
        $objForm = $this->createForm(UserProfileType::class, $objUser);
        $objForm->handleRequest($objRequest);

        if ($objForm->isSubmitted() && $objForm->isValid()) {
            $strNewPassword = $objForm->get('newPassword')->getData();
            if ($strNewPassword) {
                $objUser->setPassword(
                    $objPasswordHasher->hashPassword($objUser, $strNewPassword)
                );
            }

            $objEntityManager->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès.');
            return $this->redirectToRoute('app_user_profil');
        }

        return $this->render('user/edit_profil.html.twig', [
            'form' => $objForm,
        ]);
    }

    /**
     * Marque le compte de l'utilisateur comme supprimé (soft delete via dateSuppression).
     *
     * Vérifie le token CSRF avant de procéder, puis déconnecte l'utilisateur.
     *
     * @param EntityManagerInterface $objEntityManager Gestionnaire d'entités Doctrine
     * @param Request                $objRequest       Requête HTTP (contient le token CSRF)
     */
    #[Route('/user/profil/delete', name: 'app_user_profil_delete', methods: ['POST'])]
    public function delete(
        EntityManagerInterface $objEntityManager,
        Request $objRequest
    ): Response {
        if (!$this->isCsrfTokenValid('delete_account', $objRequest->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_user_profil');
        }

        $objUser = $this->getUser();
        $objUser->setDateSuppression(new \DateTimeImmutable());
        $objEntityManager->flush();

        return $this->redirectToRoute('app_logout');
    }

    /**
     * Affiche le profil public d'un utilisateur identifié par son id.
     *
     * Accessible sans être connecté. Seules les recettes publiées sont visibles.
     *
     * @param int $user_id Identifiant de l'utilisateur à afficher
     */
    #[Route('/user/profil/{user_id}', name: 'app_user_profil_by_id')]
    #[IsGranted('PUBLIC_ACCESS')]
    public function show(
        int               $user_id,
        UserRepository    $userRepository,
        RecetteRepository $recetteRepository
    ): Response {
        /** @var User|null $profileUser */
        $profileUser = $userRepository->find($user_id);

        if (!$profileUser || $profileUser->getDateSuppression() !== null) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        $arrRecettes   = $recetteRepository->findPublishedByUser($profileUser);
        $intLikes      = $recetteRepository->countLikesReceivedByUser($profileUser);
        $isOwnProfile  = $this->getUser() === $profileUser;

        return $this->render('user/profil_public.html.twig', [
            'profileUser'  => $profileUser,
            'arrRecettes'  => $arrRecettes,
            'intLikes'     => $intLikes,
            'isOwnProfile' => $isOwnProfile,
        ]);
    }
}