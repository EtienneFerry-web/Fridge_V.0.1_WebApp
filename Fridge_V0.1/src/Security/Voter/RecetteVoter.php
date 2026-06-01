<?php

namespace App\Security\Voter;

use App\Entity\Recette;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Gère les droits d'accès pour l'entité Recette.
 *
 * - Recettes publiées (statut 'publie') : VIEW pour tous
 * - Recettes privées (statut 'prive')   : CRUD pour le créateur et l'admin
 */
final class RecetteVoter extends Voter
{
    public const EDIT   = 'RECETTE_EDIT';
    public const DELETE = 'RECETTE_DELETE';
    public const VIEW   = 'RECETTE_VIEW';

    public function __construct(
        private readonly Security $security,
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE, self::VIEW])
            && $subject instanceof Recette;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        /** @var Recette $objRecette */
        $objRecette = $subject;
        $objUser    = $token->getUser();

        // Recettes publiées : visibles par tous
        if ($attribute === self::VIEW && $objRecette->getRecetteStatut() === 'publie') {
            return true;
        }

        // Tout le reste exige une authentification
        if (!$objUser instanceof UserInterface) {
            $vote?->addReason('Authentification requise.');
            return false;
        }

        // Admin a tous les droits
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // Le créateur a tous les droits sur ses propres recettes
        return match ($attribute) {
            self::VIEW,
            self::EDIT,
            self::DELETE => $objRecette->getCreatedBy() === $objUser,
            default      => false,
        };
    }
}
