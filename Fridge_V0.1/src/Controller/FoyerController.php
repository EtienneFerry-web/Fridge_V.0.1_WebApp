<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur du foyer (page d'accueil intermédiaire).
 */
final class FoyerController extends AbstractController
{
    /**
     * Affiche la page foyer de l'application.
     */
    #[Route('/foyer', name: 'app_foyer')]
    public function index(): Response
    {
        return $this->render('foyer/index.html.twig', [
            'controller_name' => 'FoyerController',
        ]);
    }
}
