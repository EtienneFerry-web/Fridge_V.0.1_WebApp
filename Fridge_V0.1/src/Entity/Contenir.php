<?php

namespace App\Entity;

use App\Repository\ContenirRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContenirRepository::class)]
class Contenir
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $contenirQuantite = null;

    #[ORM\ManyToOne(inversedBy: 'contenirs')]
    private ?ListeCourse $listeCourse = null;

    #[ORM\ManyToOne(inversedBy: 'contenirs')]
    private ?Ingredient $ingredient = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenirQuantite(): ?int
    {
        return $this->contenirQuantite;
    }

    public function setContenirQuantite(int $contenirQuantite): static
    {
        $this->contenirQuantite = $contenirQuantite;

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
}
