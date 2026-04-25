<?php

namespace App\Entity;

use App\Repository\RecetteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité représentant une recette de cuisine.
 *
 * Une recette est créée avec le statut 'en_attente' et doit être validée par un modérateur
 * avant d'être publiée. Elle peut contenir des étapes, des ingrédients (via Contenir),
 * des régimes alimentaires, et peut être likée ou mise en favori par les utilisateurs.
 */
#[ORM\Entity(repositoryClass: RecetteRepository::class)]
class Recette
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private string $recetteSource = 'user'; // 'user' | 'spoonacular'

    #[ORM\Column(nullable: true)]
    private ?int $spoonacularId = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $sourceUrl = null;

    #[ORM\Column(length: 150)]
    private ?string $recetteLibelle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $recetteDescription = null;

    #[ORM\Column(length: 20)]
    private ?string $recetteDifficulte = null;

    #[ORM\Column]
    private ?int $recettePortion = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $recetteTempsPrepa = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $recetteTempsCuisson = null;

    /**
     * @var Collection<int, Etape>
     */
    #[ORM\OneToMany(targetEntity: Etape::class, mappedBy: 'recette', cascade: ['persist',  'remove'])]
    private Collection $etapes;
    /**
     * @var Collection<int, LikeRecette>
     */
    #[ORM\OneToMany(targetEntity: LikeRecette::class, mappedBy: 'likeRecette')]
    private Collection $likeRecettes;

    /**
     * @var Collection<int, Favori>
     */
    #[ORM\OneToMany(targetEntity: Favori::class, mappedBy: 'favoriRecette')]
    private Collection $favoris;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $recettePhoto = null;

    #[ORM\Column(length: 20)]
    private ?string $recetteStatut = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $recetteOrigine = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $recetteCreatedAt = null;

    /**
     * @var Collection<int, Regime>
     */
    #[ORM\ManyToMany(targetEntity: Regime::class, inversedBy: 'recettes')]
    private Collection $regimes;

    /**
     * @var Collection<int, Planning>
     */
    #[ORM\OneToMany(targetEntity: Planning::class, mappedBy: 'planningRecette')]
    private Collection $plannings;

    /**
     * @var Collection<int, Contenir>
     */
    #[ORM\OneToMany(targetEntity: Contenir::class, mappedBy: 'recette', cascade: ['persist', 'remove'])]
    private Collection $contenirs;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'user_id', nullable: true)]
    private ?User $createdBy = null;

    /**
     * Initialise les collections Doctrine et applique les valeurs par défaut :
     * statut 'en_attente' (en attente de validation modérateur) et date de création à maintenant.
     */
    public function __construct()
    {
        $this->etapes           = new ArrayCollection();
        $this->likeRecettes     = new ArrayCollection();
        $this->favoris          = new ArrayCollection();
        $this->recetteCreatedAt = new \DateTime();
        $this->recetteStatut    = 'en_attente';
        $this->regimes = new ArrayCollection();
        $this->plannings = new ArrayCollection();
        $this->contenirs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecetteLibelle(): ?string
    {
        return $this->recetteLibelle;
    }

    public function setRecetteLibelle(string $recetteLibelle): static
    {
        $this->recetteLibelle = $recetteLibelle;
        return $this;
    }

    public function getRecetteDescription(): ?string
    {
        return $this->recetteDescription;
    }

    public function setRecetteDescription(?string $recetteDescription): static
    {
        $this->recetteDescription = $recetteDescription;
        return $this;
    }

    public function getRecetteDifficulte(): ?string
    {
        return $this->recetteDifficulte;
    }

    public function setRecetteDifficulte(string $recetteDifficulte): static
    {
        $this->recetteDifficulte = $recetteDifficulte;
        return $this;
    }

    public function getRecettePortion(): ?int
    {
        return $this->recettePortion;
    }

    public function setRecettePortion(int $recettePortion): static
    {
        $this->recettePortion = $recettePortion;
        return $this;
    }

    public function getRecetteTempsPrepa(): ?int
    {
        return $this->recetteTempsPrepa;
    }

    public function setRecetteTempsPrepa(int $recetteTempsPrepa): static
    {
        $this->recetteTempsPrepa = $recetteTempsPrepa;
        return $this;
    }

    public function getRecetteTempsCuisson(): ?int
    {
        return $this->recetteTempsCuisson;
    }

    public function setRecetteTempsCuisson(int $recetteTempsCuisson): static
    {
        $this->recetteTempsCuisson = $recetteTempsCuisson;
        return $this;
    }

    /**
     * @return Collection<int, Etape>
     */
    public function getEtapes(): Collection
    {
        return $this->etapes;
    }

    public function addEtape(Etape $etape): static
    {
        if (!$this->etapes->contains($etape)) {
            $this->etapes->add($etape);
            $etape->setRecette($this);
        }

        return $this;
    }

    public function removeEtape(Etape $etape): static
    {
        if ($this->etapes->removeElement($etape)) {
            
            if ($etape->getRecette() === $this) {
                $etape->setRecette(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LikeRecette>
     */
    public function getLikeRecettes(): Collection
    {
        return $this->likeRecettes;
    }

    public function addLikeRecette(LikeRecette $likeRecette): static
    {
        if (!$this->likeRecettes->contains($likeRecette)) {
            $this->likeRecettes->add($likeRecette);
            $likeRecette->setLikeRecette($this);
        }

        return $this;
    }

    public function removeLikeRecette(LikeRecette $likeRecette): static
    {
        if ($this->likeRecettes->removeElement($likeRecette)) {
            
            if ($likeRecette->getLikeRecette() === $this) {
                $likeRecette->setLikeRecette(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Favori>
     */
    public function getFavoris(): Collection
    {
        return $this->favoris;
    }

    public function addFavori(Favori $favori): static
    {
        if (!$this->favoris->contains($favori)) {
            $this->favoris->add($favori);
            $favori->setFavoriRecette($this);
        }

        return $this;
    }

    public function removeFavori(Favori $favori): static
    {
        if ($this->favoris->removeElement($favori)) {
            
            if ($favori->getFavoriRecette() === $this) {
                $favori->setFavoriRecette(null);
            }
        }

        return $this;
    }

    public function getRecettePhoto(): ?string
    {
        return $this->recettePhoto;
    }

    public function setRecettePhoto(?string $recettePhoto): static
    {
        $this->recettePhoto = $recettePhoto;

        return $this;
    }

    public function getRecetteStatut(): ?string
    {
        return $this->recetteStatut;
    }

    public function setRecetteStatut(string $recetteStatut): static
    {
        $this->recetteStatut = $recetteStatut;

        return $this;
    }

    public function getRecetteOrigine(): ?string
    {
        return $this->recetteOrigine;
    }

    public function setRecetteOrigine(?string $recetteOrigine): static
    {
        $this->recetteOrigine = $recetteOrigine;

        return $this;
    }

    public function getRecetteCreatedAt(): ?\DateTime
    {
        return $this->recetteCreatedAt;
    }

    public function setRecetteCreatedAt(\DateTime $recetteCreatedAt): static
    {
        $this->recetteCreatedAt = $recetteCreatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Regime>
     */
    public function getRegimes(): Collection
    {
        return $this->regimes;
    }

    public function addRegime(Regime $regime): static
    {
        if (!$this->regimes->contains($regime)) {
            $this->regimes->add($regime);
        }

        return $this;
    }

    public function removeRegime(Regime $regime): static
    {
        $this->regimes->removeElement($regime);

        return $this;
    }

    /**
     * @return Collection<int, Planning>
     */
    public function getPlannings(): Collection
    {
        return $this->plannings;
    }

    public function addPlanning(Planning $planning): static
    {
        if (!$this->plannings->contains($planning)) {
            $this->plannings->add($planning);
            $planning->setPlanningRecette($this);
        }

        return $this;
    }

    public function removePlanning(Planning $planning): static
    {
        if ($this->plannings->removeElement($planning)) {
            
            if ($planning->getPlanningRecette() === $this) {
                $planning->setPlanningRecette(null);
            }
        }

        return $this;
    }

    public function getContenirs(): Collection
    {
        return $this->contenirs;
    }
    
    public function addContenir(Contenir $contenir): static
    {
        if (!$this->contenirs->contains($contenir)) {
            $this->contenirs->add($contenir);
            $contenir->setRecette($this);
        }
    
        return $this;
    }
    
    public function removeContenir(Contenir $contenir): static
    {
        if ($this->contenirs->removeElement($contenir)) {
            if ($contenir->getRecette() === $this) {
                $contenir->setRecette(null);
            }
        }
    
        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }
}
