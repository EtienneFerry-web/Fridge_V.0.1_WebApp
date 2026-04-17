<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class UserVoter extends Voter
{
    public const EDIT_ROLE = 'USER_EDIT_ROLE';
    public const DELETE = 'USER_DELETE';
    public const BAN = 'USER_BAN';
    public const EDIT_PROFILE = 'USER_EDIT_PROFILE';

    public function __construct(
        private readonly Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            self::EDIT_ROLE,
            self::DELETE,
            self::BAN,
            self::EDIT_PROFILE,
        ], true)
            && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $currentUser = $token->getUser();

        if (!$currentUser instanceof User) {
            return false;
        }

        /** @var User $targetUser */
        $targetUser = $subject;

        $targetIsAdmin     = in_array('ROLE_ADMIN', $targetUser->getRoles(), true);
        $targetIsModerator = in_array('ROLE_MODERATOR', $targetUser->getRoles(), true);

        return match ($attribute) {
            self::EDIT_ROLE, self::DELETE => $this->canAdminManage($targetIsAdmin, $currentUser, $targetUser),
            self::BAN => $this->canBan($targetIsAdmin, $targetIsModerator, $currentUser, $targetUser),
            self::EDIT_PROFILE => $this->canEditProfile($targetIsAdmin, $currentUser, $targetUser),
            default => false,
        };
    }

    private function canAdminManage(bool $targetIsAdmin, User $currentUser, User $targetUser): bool
    {
        // Pas sur soi-même (pour éviter auto-suicide de compte/rôle)
        if ($currentUser === $targetUser) {
            return false;
        }
        // Admin uniquement, et pas sur un autre admin
        return $this->security->isGranted('ROLE_ADMIN') && !$targetIsAdmin;
    }

    private function canBan(bool $targetIsAdmin, bool $targetIsModerator, User $currentUser, User $targetUser): bool
    {
        // Pas sur soi-même
        if ($currentUser === $targetUser) {
            return false;
        }
        // Pas d'action sur un admin, jamais
        if ($targetIsAdmin) {
            return false;
        }
        // Un admin peut bannir tout le monde (sauf admin, déjà filtré)
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }
        // Un modérateur peut bannir uniquement les simples users
        if ($this->security->isGranted('ROLE_MODERATOR')) {
            return !$targetIsModerator;
        }
        return false;
    }

    private function canEditProfile(bool $targetIsAdmin, User $currentUser, User $targetUser): bool
    {
        // Un user peut toujours éditer son propre profil
        if ($currentUser === $targetUser) {
            return true;
        }
        // Sinon, admin uniquement, et pas sur un autre admin
        return $this->security->isGranted('ROLE_ADMIN') && !$targetIsAdmin;
    }
}