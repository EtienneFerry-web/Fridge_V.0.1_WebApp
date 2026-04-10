<?php

namespace App\Entity;

use App\Repository\RecipeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecipeRepository::class)]
class Recipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $recetteLibelle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $recetteDescription = null;

    #[ORM\Column(length: 20)]
    private ?string $recetteDifficulte = null;

    #[ORM\Column]
    private ?int $recettePortion = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $recetteTempsPrepa = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $recetteTempsCuisson = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecetteLibelle(): ?string
    {
        return $this->recetteLibelle;
    }

    public function setRecetteLibelle(string $recetteLibelle): static
    {
        $this->recetteLibelle = $recetteLibelle;

        return $this;
    }

    public function getRecetteDescription(): ?string
    {
        return $this->recetteDescription;
    }

    public function setRecetteDescription(?string $recetteDescription): static
    {
        $this->recetteDescription = $recetteDescription;

        return $this;
    }

    public function getRecetteDifficulte(): ?string
    {
        return $this->recetteDifficulte;
    }

    public function setRecetteDifficulte(string $recetteDifficulte): static
    {
        $this->recetteDifficulte = $recetteDifficulte;

        return $this;
    }

    public function getRecettePortion(): ?int
    {
        return $this->recettePortion;
    }

    public function setRecettePortion(int $recettePortion): static
    {
        $this->recettePortion = $recettePortion;

        return $this;
    }

    public function getRecetteTempsPrepa(): ?int
    {
        return $this->recetteTempsPrepa;
    }

    public function setRecetteTempsPrepa(int $recetteTempsPrepa): static
    {
        $this->recetteTempsPrepa = $recetteTempsPrepa;

        return $this;
    }

    public function getRecetteTempsCuisson(): ?int
    {
        return $this->recetteTempsCuisson;
    }

    public function setRecetteTempsCuisson(int $recetteTempsCuisson): static
    {
        $this->recetteTempsCuisson = $recetteTempsCuisson;

        return $this;
    }
}
