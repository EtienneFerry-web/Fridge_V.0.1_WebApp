<?php

namespace App\Entity;

use App\Repository\FoyerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /**
     * @var Collection<int, Stocker>
     */
    #[ORM\OneToMany(targetEntity: Stocker::class, mappedBy: 'foyer')]
    private Collection $stockers;

    /**
     * @var Collection<int, Repas>
     */
    #[ORM\OneToMany(targetEntity: Repas::class, mappedBy: 'foyer')]
    private Collection $repas;

    public function __construct()
    {
        $this->stockers = new ArrayCollection();
        $this->repas = new ArrayCollection();
    }

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
            $stocker->setFoyer($this);
        }

        return $this;
    }

    public function removeStocker(Stocker $stocker): static
    {
        if ($this->stockers->removeElement($stocker)) {
            // set the owning side to null (unless already changed)
            if ($stocker->getFoyer() === $this) {
                $stocker->setFoyer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Repas>
     */
    public function getRepas(): Collection
    {
        return $this->repas;
    }

    public function addRepa(Repas $repa): static
    {
        if (!$this->repas->contains($repa)) {
            $this->repas->add($repa);
            $repa->setFoyer($this);
        }

        return $this;
    }

    public function removeRepa(Repas $repa): static
    {
        if ($this->repas->removeElement($repa)) {
            // set the owning side to null (unless already changed)
            if ($repa->getFoyer() === $this) {
                $repa->setFoyer(null);
            }
        }

        return $this;
    }
}
