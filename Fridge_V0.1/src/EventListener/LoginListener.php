<?php

namespace App\EventListener;

use App\Entity\User;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

/**
 * Écouteur d'événement déclenché à chaque tentative de connexion.
 *
 * Bloque l'authentification si le compte utilisateur a été supprimé en soft delete (dateSuppression non null).
 */
#[AsEventListener(event: CheckPassportEvent::class)]
class LoginListener
{
    /**
     * Vérifie que le compte n'est pas supprimé avant de laisser passer l'authentification.
     *
     * @throws CustomUserMessageAuthenticationException si le compte a une date de suppression renseignée
     */
    public function __invoke(CheckPassportEvent $objEvent): void
    {
        $objUser = $objEvent->getPassport()->getUser();

        if ($objUser instanceof User && $objUser->getDateSuppression() !== null) {
            throw new CustomUserMessageAuthenticationException(
                'Ce compte a été supprimé. Contactez le support si besoin.'
            );
        }
    }
}