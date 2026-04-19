<?php

namespace App\Repository;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Persistence\Repository\ResetPasswordRequestRepositoryTrait;
use SymfonyCasts\Bundle\ResetPassword\Persistence\ResetPasswordRequestRepositoryInterface;

/**
 * Repository des demandes de réinitialisation de mot de passe.
 *
 * Implémente ResetPasswordRequestRepositoryInterface du bundle SymfonyCasts.
 * Le trait ResetPasswordRequestRepositoryTrait fournit les méthodes de persistance/suppression des tokens.
 *
 * @extends ServiceEntityRepository<ResetPasswordRequest>
 */
class ResetPasswordRequestRepository extends ServiceEntityRepository implements ResetPasswordRequestRepositoryInterface
{
    use ResetPasswordRequestRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResetPasswordRequest::class);
    }

    /**
     * Fabrique une nouvelle entité ResetPasswordRequest (factory requise par le bundle).
     *
     * @param User               $user         L'utilisateur concerné par la réinitialisation
     * @param \DateTimeInterface $expiresAt    Date d'expiration du token
     * @param string             $selector     Sélecteur public du token
     * @param string             $hashedToken  Token haché à stocker en base
     */
    public function createResetPasswordRequest(object $user, \DateTimeInterface $expiresAt, string $selector, string $hashedToken): ResetPasswordRequestInterface
    {
        return new ResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);
    }
}
