<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EditUserProfilController extends AbstractController
{
    #[Route('/edit/user/profil', name: 'app_edit_user_profil')]
    public function index(): Response
    {
        return $this->render('edit_user_profil/index.html.twig', [
            'controller_name' => 'EditUserProfilController',
        ]);
    }
}
