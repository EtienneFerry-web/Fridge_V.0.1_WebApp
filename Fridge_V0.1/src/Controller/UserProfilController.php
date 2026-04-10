<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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

    #[Route('/user/profil/{id}', name: 'app_user_profil_by_id')]
    public function show(int $id): Response
    {
        // Stub — will be implemented with the User management feature
        return $this->render('user/profil.html.twig');
    }

    
}