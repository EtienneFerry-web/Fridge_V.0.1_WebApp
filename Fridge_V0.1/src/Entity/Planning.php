<?php

namespace App\Entity;

use App\Repository\PlanningRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanningRepository::class)]
class Planning
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $planningJour = null;

    #[ORM\Column(length: 20)]
    private ?string $planningMoment = null;

    #[ORM\ManyToOne(inversedBy: 'plannings')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', nullable: true)]
    private ?User $planningUser = null;

    #[ORM\ManyToOne(inversedBy: 'plannings')]
    private ?Recette $planningRecette = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlanningJour(): ?string
    {
        return $this->planningJour;
    }

    public function setPlanningJour(string $planningJour): static
    {
        $this->planningJour = $planningJour;

        return $this;
    }

    public function getPlanningMoment(): ?string
    {
        return $this->planningMoment;
    }

    public function setPlanningMoment(string $planningMoment): static
    {
        $this->planningMoment = $planningMoment;

        return $this;
    }

    public function getPlanningUser(): ?User
    {
        return $this->planningUser;
    }

    public function setPlanningUser(?User $planningUser): static
    {
        $this->planningUser = $planningUser;

        return $this;
    }

    public function getPlanningRecette(): ?Recette
    {
        return $this->planningRecette;
    }

    public function setPlanningRecette(?Recette $planningRecette): static
    {
        $this->planningRecette = $planningRecette;

        return $this;
    }
}
