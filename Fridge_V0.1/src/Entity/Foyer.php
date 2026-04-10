<?php

namespace App\Entity;

use App\Repository\FoyerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FoyerRepository::class)]
class Foyer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $foyerNom = null;

    #[ORM\Column]
    private ?int $foyerNombrePers = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $foyerDateCreation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFoyerNom(): ?string
    {
        return $this->foyerNom;
    }

    public function setFoyerNom(string $foyerNom): static
    {
        $this->foyerNom = $foyerNom;

        return $this;
    }

    public function getFoyerNombrePers(): ?int
    {
        return $this->foyerNombrePers;
    }

    public function setFoyerNombrePers(int $foyerNombrePers): static
    {
        $this->foyerNombrePers = $foyerNombrePers;

        return $this;
    }

    public function getFoyerDateCreation(): ?\DateTimeImmutable
    {
        return $this->foyerDateCreation;
    }

    public function setFoyerDateCreation(\DateTimeImmutable $foyerDateCreation): static
    {
        $this->foyerDateCreation = $foyerDateCreation;

        return $this;
    }
}
