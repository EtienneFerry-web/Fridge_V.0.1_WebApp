<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Contrôleur d'authentification (connexion / déconnexion).
 *
 * La déconnexion est gérée directement par le pare-feu Symfony via la configuration security.yaml.
 */
class SecurityController extends AbstractController
{
    /**
     * Affiche le formulaire de connexion avec le dernier identifiant saisi et l'erreur éventuelle.
     *
     * @param AuthenticationUtils $authenticationUtils Utilitaire Symfony pour récupérer erreur et dernier username
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * Point d'entrée de la déconnexion — intercepté par le pare-feu Symfony avant exécution.
     *
     * Ce corps de méthode ne sera jamais atteint.
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
