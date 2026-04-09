<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ResetPwdController extends AbstractController
{
    #[Route('/reset/pwd', name: 'app_reset_pwd')]
    public function index(): Response
    {
        return $this->render('reset_pwd/index.html.twig', [
            'controller_name' => 'ResetPwdController',
        ]);
    }
}
