<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur de redirection vers la page d'édition du profil utilisateur.
 *
 * @deprecated Préférer UserProfilController::edit() qui gère réellement le formulaire d'édition.
 */
final class EditUserProfilController extends AbstractController
{
    /**
     * Affiche la vue d'édition du profil de l'utilisateur connecté.
     */
    #[Route('/edit/user/profil', name: 'app_edit_user_profil')]
    public function index(): Response
    {
        return $this->render('edit_user_profil/index.html.twig', [
            'user' => $this->getUser(), 
        ]);
    }
}