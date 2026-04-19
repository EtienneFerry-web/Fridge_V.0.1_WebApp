<?php

namespace App\Entity;

use App\Repository\RegimeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité représentant un régime alimentaire (ex. végétarien, sans gluten).
 *
 * Relation ManyToMany avec User (préférences) et Recette (régimes compatibles).
 * Les méthodes add/remove synchronisent les deux côtés de la relation bidirectionnelle.
 */
#[ORM\Entity(repositoryClass: RegimeRepository::class)]
class Regime
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $regimeLibelle = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'regimes')]
    private Collection $regimeUsers;

    /**
     * @var Collection<int, Recette>
     */
    #[ORM\ManyToMany(targetEntity: Recette::class, mappedBy: 'regimes')]
    private Collection $recettes;

    public function __construct()
    {
        $this->regimeUsers = new ArrayCollection();
        $this->recettes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRegimeLibelle(): ?string
    {
        return $this->regimeLibelle;
    }

    public function setRegimeLibelle(string $regimeLibelle): static
    {
        $this->regimeLibelle = $regimeLibelle;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getRegimeUsers(): Collection
    {
        return $this->regimeUsers;
    }

    public function addRegimeUser(User $regimeUser): static
    {
        if (!$this->regimeUsers->contains($regimeUser)) {
            $this->regimeUsers->add($regimeUser);
            $regimeUser->addRegime($this);
        }

        return $this;
    }

    public function removeRegimeUser(User $regimeUser): static
    {
        if ($this->regimeUsers->removeElement($regimeUser)) {
            $regimeUser->removeRegime($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Recette>
     */
    public function getRecettes(): Collection
    {
        return $this->recettes;
    }

    public function addRecette(Recette $recette): static
    {
        if (!$this->recettes->contains($recette)) {
            $this->recettes->add($recette);
            $recette->addRegime($this);
        }

        return $this;
    }

    public function removeRecette(Recette $recette): static
    {
        if ($this->recettes->removeElement($recette)) {
            $recette->removeRegime($this);
        }

        return $this;
    }
}
