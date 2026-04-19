<?php

namespace App\Entity;

use App\Repository\ResetPasswordRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestTrait;

/**
 * Entité représentant une demande de réinitialisation de mot de passe.
 *
 * Gérée par le bundle SymfonyCasts/ResetPasswordBundle via le trait ResetPasswordRequestTrait.
 * Stocke le token (haché), le sélecteur et la date d'expiration associés à un utilisateur.
 */
#[ORM\Entity(repositoryClass: ResetPasswordRequestRepository::class)]
class ResetPasswordRequest implements ResetPasswordRequestInterface
{
    use ResetPasswordRequestTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'reset_id')]
    private ?int $intId = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', nullable: false)]
    private ?User $user = null;

    /**
     * Initialise la demande de réinitialisation via le trait du bundle.
     *
     * @param User               $user         Utilisateur concerné
     * @param \DateTimeInterface $expiresAt    Date d'expiration du token
     * @param string             $selector     Sélecteur public du token (non secret)
     * @param string             $hashedToken  Token haché stocké en base
     */
    public function __construct(User $user, \DateTimeInterface $expiresAt, string $selector, string $hashedToken)
    {
        $this->user = $user;
        $this->initialize($expiresAt, $selector, $hashedToken);
    }

    public function getId(): ?int
    {
        return $this->intId;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
