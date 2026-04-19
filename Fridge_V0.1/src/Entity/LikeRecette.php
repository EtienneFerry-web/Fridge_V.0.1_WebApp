<?php

namespace App\Entity;

use App\Repository\LikeRecetteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité représentant le like d'une recette par un utilisateur.
 *
 * Table de jointure enrichie entre User et Recette, avec la date du like.
 * Un utilisateur ne peut liker une recette qu'une seule fois (géré dans LikeController).
 */
#[ORM\Entity(repositoryClass: LikeRecetteRepository::class)]
class LikeRecette
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $likeDate = null;

    #[ORM\ManyToOne(inversedBy: 'likeRecette')]
    #[ORM\JoinColumn(referencedColumnName: 'user_id', nullable: false)]
    private ?User $likeUser = null;

    #[ORM\ManyToOne(inversedBy: 'likeRecettes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Recette $likeRecette = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLikeDate(): ?\DateTimeImmutable
    {
        return $this->likeDate;
    }

    public function setLikeDate(\DateTimeImmutable $likeDate): static
    {
        $this->likeDate = $likeDate;

        return $this;
    }

    public function getLikeUser(): ?User
    {
        return $this->likeUser;
    }

    public function setLikeUser(?User $likeUser): static
    {
        $this->likeUser = $likeUser;

        return $this;
    }

    public function getLikeRecette(): ?Recette
    {
        return $this->likeRecette;
    }

    public function setLikeRecette(?Recette $likeRecette): static
    {
        $this->likeRecette = $likeRecette;

        return $this;
    }
}
