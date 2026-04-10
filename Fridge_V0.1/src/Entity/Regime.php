<?php

namespace App\Entity;

use App\Repository\RegimeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RegimeRepository::class)]
class Regime
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $regimeLibelle = null;

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
}
