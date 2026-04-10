<?php

namespace App\EventListener;

use App\Entity\User;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

#[AsEventListener(event: CheckPassportEvent::class)]
class LoginListener
{
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