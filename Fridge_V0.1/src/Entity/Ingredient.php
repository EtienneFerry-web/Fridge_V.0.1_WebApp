<?php

namespace App\Entity;

use App\Repository\IngredientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IngredientRepository::class)]
class Ingredient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $ingredientLibelle = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $ingredientType = null;

    /**
     * @var Collection<int, Stocker>
     */
    #[ORM\OneToMany(targetEntity: Stocker::class, mappedBy: 'ingredient')]
    private Collection $stockers;

    /**
     * @var Collection<int, Contenir>
     */
    #[ORM\OneToMany(targetEntity: Contenir::class, mappedBy: 'ingredient')]
    private Collection $contenirs;

    public function __construct()
    {
        $this->stockers = new ArrayCollection();
        $this->contenirs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIngredientLibelle(): ?string
    {
        return $this->ingredientLibelle;
    }

    public function setIngredientLibelle(string $ingredientLibelle): static
    {
        $this->ingredientLibelle = $ingredientLibelle;

        return $this;
    }

    public function getIngredientType(): ?string
    {
        return $this->ingredientType;
    }

    public function setIngredientType(?string $ingredientType): static
    {
        $this->ingredientType = $ingredientType;

        return $this;
    }

    /**
     * @return Collection<int, Stocker>
     */
    public function getStockers(): Collection
    {
        return $this->stockers;
    }

    public function addStocker(Stocker $stocker): static
    {
        if (!$this->stockers->contains($stocker)) {
            $this->stockers->add($stocker);
            $stocker->setIngredient($this);
        }

        return $this;
    }

    public function removeStocker(Stocker $stocker): static
    {
        if ($this->stockers->removeElement($stocker)) {
            // set the owning side to null (unless already changed)
            if ($stocker->getIngredient() === $this) {
                $stocker->setIngredient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Contenir>
     */
    public function getContenirs(): Collection
    {
        return $this->contenirs;
    }

    public function addContenir(Contenir $contenir): static
    {
        if (!$this->contenirs->contains($contenir)) {
            $this->contenirs->add($contenir);
            $contenir->setIngredient($this);
        }

        return $this;
    }

    public function removeContenir(Contenir $contenir): static
    {
        if ($this->contenirs->removeElement($contenir)) {
            // set the owning side to null (unless already changed)
            if ($contenir->getIngredient() === $this) {
                $contenir->setIngredient(null);
            }
        }

        return $this;
    }
}
