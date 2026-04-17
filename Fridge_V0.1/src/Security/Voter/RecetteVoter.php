<?php

namespace App\Security\Voter;

use App\Entity\Recette;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class RecetteVoter extends Voter
{
    public const EDIT = 'RECETTE_EDIT';
    public const DELETE = 'RECETTE_DELETE';

    public function __construct(
        private readonly Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE])
            && $subject instanceof Recette;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            $vote?->addReason('L\'utilisateur doit être connecté.');

            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_MODERATOR')) {
            return true;
        }

        $recette = $subject;

        return match ($attribute) {
            self::EDIT, self::DELETE => $recette->getCreatedBy() === $user,
            default => false,
        };
    }
}
