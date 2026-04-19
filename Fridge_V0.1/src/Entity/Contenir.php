<?php

namespace App\Entity;

use App\Repository\ContenirRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité représentant une ligne d'ingrédient dans une liste de courses ou une recette.
 *
 * Fait le lien entre un ingrédient et une ListeCourse (et optionnellement une Recette source).
 * Le champ contenirEstCoche indique si l'utilisateur a coché cet ingrédient dans sa liste de courses.
 */
#[ORM\Entity(repositoryClass: ContenirRepository::class)]
class Contenir
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?float $contenirQuantite = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $contenirUnite = null;

    #[ORM\ManyToOne(inversedBy: 'contenirs')]
    private ?ListeCourse $listeCourse = null;

    #[ORM\ManyToOne(inversedBy: 'contenirs')]
    private ?Ingredient $ingredient = null;

    #[ORM\ManyToOne(inversedBy: 'contenirs')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Recette $recette = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $contenirEstCoche = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenirQuantite(): ?float
    {
        return $this->contenirQuantite;
    }

    public function setContenirQuantite(?float $contenirQuantite): static
    {
        $this->contenirQuantite = $contenirQuantite;

        return $this;
    }

    public function getContenirUnite(): ?string
    {
        return $this->contenirUnite;
    }

    public function setContenirUnite(?string $contenirUnite): static
    {
        $this->contenirUnite = $contenirUnite;

        return $this;
    }

    public function getListeCourse(): ?ListeCourse
    {
        return $this->listeCourse;
    }

    public function setListeCourse(?ListeCourse $listeCourse): static
    {
        $this->listeCourse = $listeCourse;

        return $this;
    }

    public function getIngredient(): ?Ingredient
    {
        return $this->ingredient;
    }

    public function setIngredient(?Ingredient $ingredient): static
    {
        $this->ingredient = $ingredient;

        return $this;
    }

    public function getRecette(): ?Recette
    {
        return $this->recette;
    }

    public function setRecette(?Recette $recette): static
    {
        $this->recette = $recette;

        return $this;
    }

    public function isContenirEstCoche(): bool
    {
        return $this->contenirEstCoche;
    }

    public function setContenirEstCoche(bool $contenirEstCoche): static
    {
        $this->contenirEstCoche = $contenirEstCoche;

        return $this;
    }
}