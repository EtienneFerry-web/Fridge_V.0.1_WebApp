<?php

namespace App\Entity;

use App\Repository\ListeCourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ListeCourseRepository::class)]
class ListeCourse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $listeLibelle = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $listeDateCreation = null;

    #[ORM\Column(length: 20)]
    private ?string $listeStatut = null;

    /**
     * @var Collection<int, Contenir>
     */
    #[ORM\OneToMany(targetEntity: Contenir::class, mappedBy: 'listeCourse')]
    private Collection $contenirs;

    public function __construct()
    {
        $this->contenirs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getListeLibelle(): ?string
    {
        return $this->listeLibelle;
    }

    public function setListeLibelle(string $listeLibelle): static
    {
        $this->listeLibelle = $listeLibelle;

        return $this;
    }

    public function getListeDateCreation(): ?\DateTimeImmutable
    {
        return $this->listeDateCreation;
    }

    public function setListeDateCreation(\DateTimeImmutable $listeDateCreation): static
    {
        $this->listeDateCreation = $listeDateCreation;

        return $this;
    }

    public function getListeStatut(): ?string
    {
        return $this->listeStatut;
    }

    public function setListeStatut(string $listeStatut): static
    {
        $this->listeStatut = $listeStatut;

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
            $contenir->setListeCourse($this);
        }

        return $this;
    }

    public function removeContenir(Contenir $contenir): static
    {
        if ($this->contenirs->removeElement($contenir)) {
            // set the owning side to null (unless already changed)
            if ($contenir->getListeCourse() === $this) {
                $contenir->setListeCourse(null);
            }
        }

        return $this;
    }
}
