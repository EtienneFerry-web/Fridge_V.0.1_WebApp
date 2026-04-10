<?php

namespace App\Entity;

use App\Repository\StockerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StockerRepository::class)]
class Stocker
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $stockerQuantiteDispo = null;

    #[ORM\ManyToOne(inversedBy: 'stockers')]
    private ?Foyer $foyer = null;

    #[ORM\ManyToOne(inversedBy: 'stockers')]
    private ?Ingredient $ingredient = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStockerQuantiteDispo(): ?string
    {
        return $this->stockerQuantiteDispo;
    }

    public function setStockerQuantiteDispo(string $stockerQuantiteDispo): static
    {
        $this->stockerQuantiteDispo = $stockerQuantiteDispo;

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
