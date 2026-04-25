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
 * Logique de visibilité :
 * - Recettes Spoonacular (source 'spoonacular') :
 *     VIEW   : tout le monde (publiques)
 *     EDIT   : personne (contenu externe non modifiable)
 *     DELETE : admin uniquement
 * - Recettes utilisateur (source 'user', statut 'prive') :
 *     VIEW   : créateur uniquement (+ admin)
 *     EDIT   : créateur uniquement (+ admin)
 *     DELETE : créateur uniquement (+ admin)
 */
final class RecetteVoter extends Voter
{
    public const EDIT   = 'RECETTE_EDIT';
    public const DELETE = 'RECETTE_DELETE';
    public const VIEW   = 'RECETTE_VIEW';

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
        /** @var Recette $objRecette */
        $objRecette = $subject;
        $objUser    = $token->getUser();

        // === Recettes Spoonacular ===
        if ($objRecette->getRecetteSource() === 'spoonacular') {
            return match ($attribute) {
                // Visibles par tous, même les visiteurs anonymes
                self::VIEW   => true,
                // Pas d'édition possible pour les contenus externes
                self::EDIT   => false,
                // Suppression réservée à l'admin (modération de contenu inapproprié par exemple)
                self::DELETE => $this->security->isGranted('ROLE_ADMIN'),
                default      => false,
            };
        }

        // === Recettes utilisateur (source 'user') ===

        // Tout le reste exige une authentification
        if (!$objUser instanceof UserInterface) {
            $vote?->addReason('L\'utilisateur doit être connecté pour accéder à cette recette privée.');
            return false;
        }

        // Admin a tous les droits sur les recettes utilisateur
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