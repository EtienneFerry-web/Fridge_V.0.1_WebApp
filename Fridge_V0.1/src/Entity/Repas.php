<?php

namespace App\Entity;

use App\Repository\RepasRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité représentant un repas planifié pour un foyer à une date donnée.
 *
 * Fonctionnalité liée au foyer partagé, actuellement en cours de développement.
 */
#[ORM\Entity(repositoryClass: RepasRepository::class)]
class Repas
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $repasDate = null;

    #[ORM\Column(length: 20)]
    private ?string $repasType = null;

    #[ORM\ManyToOne(inversedBy: 'repas')]
    private ?Foyer $foyer = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRepasDate(): ?\DateTimeImmutable
    {
        return $this->repasDate;
    }

    public function setRepasDate(\DateTimeImmutable $repasDate): static
    {
        $this->repasDate = $repasDate;

        return $this;
    }

    public function getRepasType(): ?string
    {
        return $this->repasType;
    }

    public function setRepasType(string $repasType): static
    {
        $this->repasType = $repasType;

        return $this;
    }

    public function getFoyer(): ?Foyer
    {
        return $this->foyer;
    }

    public function setFoyer(?Foyer $foyer): static
    {
        $this->foyer = $foyer;

        return $this;
    }
}
