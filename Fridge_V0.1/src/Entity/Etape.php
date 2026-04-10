<?php

namespace App\Entity;

use App\Repository\EtapeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EtapeRepository::class)]
class Etape
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $etapeNumero = null;

    #[ORM\Column(length: 150)]
    private ?string $etapeLibelle = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $etapeDescription = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $etapeDuree = null;

    #[ORM\ManyToOne(inversedBy: 'etapes')]
    private ?Recette $recette = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEtapeNumero(): ?int
    {
        return $this->etapeNumero;
    }

    public function setEtapeNumero(int $etapeNumero): static
    {
        $this->etapeNumero = $etapeNumero;

        return $this;
    }

    public function getEtapeLibelle(): ?string
    {
        return $this->etapeLibelle;
    }

    public function setEtapeLibelle(string $etapeLibelle): static
    {
        $this->etapeLibelle = $etapeLibelle;

        return $this;
    }

    public function getEtapeDescription(): ?string
    {
        return $this->etapeDescription;
    }

    public function setEtapeDescription(string $etapeDescription): static
    {
        $this->etapeDescription = $etapeDescription;

        return $this;
    }

    public function getEtapeDuree(): ?int
    {
        return $this->etapeDuree;
    }

    public function setEtapeDuree(?int $etapeDuree): static
    {
        $this->etapeDuree = $etapeDuree;

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
}
