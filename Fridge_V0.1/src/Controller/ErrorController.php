<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;

final class ErrorController extends AbstractController
{
    public function show(FlattenException $exception): Response
    {
        $statusCode = $exception->getStatusCode();

        return match($statusCode) {
            403 => $this->render('errors/error403.html.twig', [
                'message' => $exception->getMessage(),
            ], new Response('', 403)),

            404 => $this->render('errors/error404.html.twig', [
                'message' => $exception->getMessage(),
            ], new Response('', 404)),

            default => $this->render('errors/error500.html.twig', [
                'message' => $exception->getMessage(),
            ], new Response('', $statusCode)),
        };
    }
}
