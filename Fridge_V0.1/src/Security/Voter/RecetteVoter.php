<?php

namespace App\Security\Voter;

use App\Entity\Recette;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Gère les droits d'accès pour l'entité Recette.
 */
final class RecetteVoter extends Voter
{
    public const EDIT = 'RECETTE_EDIT';
    public const DELETE = 'RECETTE_DELETE';
    public const VIEW = 'RECETTE_VIEW';

    public function __construct(
        private readonly Security $security,
    ) {
    }

    /**
     * Détermine si le voter supporte l'attribut et l'objet fournis.
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE, self::VIEW])
            && $subject instanceof Recette;
    }

    /**
     * Vote sur la permission accordée ou non.
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        $recette = $subject;

        if ($attribute === self::VIEW && $recette->getRecetteStatut() === 'publie') {
            return true;
        }

        if (!$user instanceof UserInterface) {
            $vote?->addReason('L\'utilisateur doit être connecté.');

            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_MODERATOR')) {
            return true;
        }

        return match ($attribute) {
            self::VIEW        => $recette->getCreatedBy() === $user,
            self::EDIT,
            self::DELETE      => $recette->getCreatedBy() === $user,
            default           => false,
        };
    }
    
}
