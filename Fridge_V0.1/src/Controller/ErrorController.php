<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contrôleur de gestion des pages d'erreur HTTP personnalisées.
 *
 * Rend les templates Twig dédiés pour les codes 403, 404 et 5xx.
 */
final class ErrorController extends AbstractController
{
    /**
     * Affiche la page d'erreur adaptée au code HTTP de l'exception.
     *
     * @param FlattenException $exception Exception aplatie fournie par le kernel Symfony
     */
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
