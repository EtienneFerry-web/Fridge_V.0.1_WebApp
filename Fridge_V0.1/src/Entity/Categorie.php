<?php

namespace App\Entity;

use App\Repository\CategorieRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 80)]
    private ?string $categorieLibelle = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategorieLibelle(): ?string
    {
        return $this->categorieLibelle;
    }

    public function setCategorieLibelle(string $categorieLibelle): static
    {
        $this->categorieLibelle = $categorieLibelle;

        return $this;
    }
}
