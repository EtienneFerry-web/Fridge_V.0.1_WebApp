<?php

namespace App\Entity;

use App\Repository\FavoriRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité représentant la mise en favori d'une recette par un utilisateur.
 *
 * Table de jointure enrichie entre User et Recette, avec la date d'ajout aux favoris.
 */
#[ORM\Entity(repositoryClass: FavoriRepository::class)]
class Favori
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $favoriDate = null;

    #[ORM\ManyToOne(inversedBy: 'favoris')]
    #[ORM\JoinColumn(referencedColumnName: 'user_id',nullable: false)]
    private ?User $favoriUser = null;

    #[ORM\ManyToOne(inversedBy: 'favoris')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Recette $favoriRecette = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFavoriDate(): ?\DateTimeImmutable
    {
        return $this->favoriDate;
    }

    public function setFavoriDate(\DateTimeImmutable $favoriDate): static
    {
        $this->favoriDate = $favoriDate;

        return $this;
    }

    public function getFavoriUser(): ?User
    {
        return $this->favoriUser;
    }

    public function setFavoriUser(?User $favoriUser): static
    {
        $this->favoriUser = $favoriUser;

        return $this;
    }

    public function getFavoriRecette(): ?Recette
    {
        return $this->favoriRecette;
    }

    public function setFavoriRecette(?Recette $favoriRecette): static
    {
        $this->favoriRecette = $favoriRecette;

        return $this;
    }
}
