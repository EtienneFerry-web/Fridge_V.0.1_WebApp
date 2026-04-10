<?php

namespace App\Controller;

use App\Form\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class UserProfilController extends AbstractController
{
    #[Route('/user/profil', name: 'app_user_profil')]
    public function index(): Response
    {
        return $this->render('user/profil.html.twig');
    }

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

    #[Route('/user/profil/{user_id}', name: 'app_user_profil_by_id')]
    public function show(int $user_id): Response
    {
        return $this->render('user/profil.html.twig');
    }
}