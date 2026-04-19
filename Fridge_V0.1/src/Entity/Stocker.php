<?php

namespace App\Entity;

use App\Repository\StockerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité représentant une entrée dans le stock personnel d'un utilisateur.
 *
 * Associe un ingrédient à un utilisateur avec une quantité disponible, une unité de mesure,
 * un seuil d'alerte optionnel et une date de péremption optionnelle.
 * Le champ foyer est prévu pour une future fonctionnalité de stock partagé.
 */
#[ORM\Entity(repositoryClass: StockerRepository::class)]
class Stocker
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $stockerQuantiteDispo = null;

    #[ORM\Column(length: 20)]
    private ?string $stockerUnite = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $stockerSeuil = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $stockerDatePeremption = null;

    #[ORM\ManyToOne(inversedBy: 'stockers')]
    private ?Foyer $foyer = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', nullable: true)]
    private ?User $user = null;

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

    public function getStockerUnite(): ?string 
    { 
        return $this->stockerUnite; 
    }

    public function setStockerUnite(string $v): static 
    { 
        $this->stockerUnite = $v; return $this; 
    }

    public function getStockerSeuil(): ?string 
    { 
        return $this->stockerSeuil; 
    }

    public function setStockerSeuil(?string $v): static 
    { 
        $this->stockerSeuil = $v; return $this; 
    }

    public function getStockerDatePeremption(): ?\DateTimeImmutable 
    { 
        return $this->stockerDatePeremption; 
    }

    public function setStockerDatePeremption(?\DateTimeImmutable $v): static 
    { 
        $this->stockerDatePeremption = $v; return $this; 
    }

    public function getUser(): ?User 
    { 
        return $this->user; 
    }

    public function setUser(?User $v): static 
    { 
        $this->user = $v; return $this; 
    }

}
