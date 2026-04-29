<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entité représentant un utilisateur de l'application.
 *
 * Implémente UserInterface et PasswordAuthenticatedUserInterface pour l'intégration
 * avec le système de sécurité Symfony. L'email et le pseudo sont uniques.
 * La date d'inscription est initialisée automatiquement via un lifecycle callback PrePersist.
 * La suppression de compte est gérée en soft delete via dateSuppression.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['strEmail'])]
#[UniqueEntity(fields: ['strEmail'], message: 'There is already an account with this email')]
#[UniqueEntity(fields: ['strUsername'], message: 'This pseudo is already taken')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'user_id')]
    private ?int $intId = null;

    #[Assert\NotBlank(message: 'Entrez votre email')]
    #[Assert\Email(message: 'Format email invalide')]
    #[ORM\Column(name: 'user_email', length: 180)]
    private ?string $strEmail = null;

    #[Assert\NotBlank(message: 'Entrez votre nom')]
    #[ORM\Column(name: 'user_name', length: 255)] 
    private ?string $strName = null;

    #[Assert\NotBlank(message:'Entrez votre prénom')]
    #[ORM\Column(name: 'user_firstname', length: 255)] 
    private ?string $strFirstname = null;

    #[Assert\NotBlank(message: 'Entrez votre pseudo')]
    #[ORM\Column(name: 'user_username', length: 255, unique: true)] 
    private ?string $strUsername = null;
    /**
     * @var list<string> The user roles
     */
    #[ORM\Column(name: 'user_roles')]
    private array $arrRoles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(name: 'user_password')]
    private ?string $strPassword = null;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column(name: 'user_date_inscription', type: 'datetime_immutable')]
    private ?\DateTimeImmutable $dateInscription = null;

    #[ORM\Column(name: 'user_date_suppression', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateSuppression = null;

    /**
     * @var Collection<int, Regime>
     */
    #[ORM\ManyToMany(targetEntity: Regime::class, inversedBy: 'regimeUsers')]
    #[ORM\JoinTable(name: 'user_regime',
        joinColumns: [new ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'regime_id', referencedColumnName: 'id')]
    )]
    private Collection $regimes;

    /**
     * @var Collection<int, LikeRecette>
     */
    #[ORM\OneToMany(targetEntity: LikeRecette::class, mappedBy: 'likeUser')]
    private Collection $likeRecette;

    /**
     * @var Collection<int, Favori>
     */
    #[ORM\OneToMany(targetEntity: Favori::class, mappedBy: 'favoriUser')]
    private Collection $favoris;

    /**
     * @var Collection<int, Planning>
     */
    #[ORM\OneToMany(targetEntity: Planning::class, mappedBy: 'planningUser')]
    private Collection $plannings;

    /**
     * @var Collection<int, Liste>
     */
    #[ORM\OneToMany(targetEntity: Liste::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $listes;


    /**
     * Initialise les collections Doctrine (obligatoire pour les relations OneToMany / ManyToMany).
     */
    public function __construct()
    {
        $this->regimes = new ArrayCollection();
        $this->likeRecette = new ArrayCollection();
        $this->favoris = new ArrayCollection();
        $this->plannings = new ArrayCollection();
        $this->listes = new ArrayCollection();
    }

    // --- ID ---

    public function getId(): ?int
    {
        return $this->intId;
    }

    // --- EMAIL ---

    public function getStrEmail(): ?string
    {
        return $this->strEmail;
    }

    public function setStrEmail(string $strEmail): static
    {
        $this->strEmail = $strEmail;
        return $this;
    }

    // --- NAME ---

    public function getStrName(): ?string
    {
        return $this->strName;
    }

    public function setStrName(string $strName): static
    {
        $this->strName = $strName;
        return $this;
    }

    // --- FIRSTNAME ---

    public function getStrFirstname(): ?string
    {
        return $this->strFirstname;
    }

    public function setStrFirstname(string $strFirstname): static
    {
        $this->strFirstname = $strFirstname;
        return $this;
    }

    // --- USERNAME (PSEUDO) ---

    public function getStrUsername(): ?string
    {
        return $this->strUsername;
    }

    public function setStrUsername(string $strUsername): static
    {
        $this->strUsername = $strUsername;
        return $this;
    }

    /**
     * Identifiant unique utilisé par Symfony pour reconnaître l'utilisateur (l'email ici).
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->strEmail;
    }

    /**
     * Retourne les rôles de l'utilisateur. ROLE_USER est toujours inclus par défaut.
     *
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->arrRoles;

        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $arrRoles
     */
    public function setRoles(array $arrRoles): static
    {
        $this->arrRoles = $arrRoles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->strPassword;
    }

    public function setPassword(string $strPassword): static
    {
        $this->strPassword = $strPassword;

        return $this;
    }


    /**
     * Sérialise l'utilisateur pour la session en remplaçant le mot de passe en clair
     * par son empreinte CRC32C, évitant ainsi de stocker le hash bcrypt complet en session.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->strPassword);

        return $data;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    // --- DATE INSCRIPTION ---

    public function getDateInscription(): ?\DateTimeImmutable
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeImmutable $dateInscription): static
    {
        $this->dateInscription = $dateInscription;
        return $this;
    }

    // --- DATE SUPPRESSION ---

    public function getDateSuppression(): ?\DateTimeImmutable
    {
        return $this->dateSuppression;
    }

    public function setDateSuppression(?\DateTimeImmutable $dateSuppression): static
    {
        $this->dateSuppression = $dateSuppression;
        return $this;
    }
    
    /**
     * Initialise automatiquement la date d'inscription à la première persistance en base.
     * Déclenché par le lifecycle callback PrePersist de Doctrine.
     */
    #[ORM\PrePersist]
    public function initDateInscription(): void
    {
        $this->dateInscription = new \DateTimeImmutable();
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
     * @return Collection<int, LikeRecette>
     */
    public function getLikeRecette(): Collection
    {
        return $this->likeRecette;
    }

    public function addLikeRecette(LikeRecette $likeRecette): static
    {
        if (!$this->likeRecette->contains($likeRecette)) {
            $this->likeRecette->add($likeRecette);
            $likeRecette->setLikeUser($this);
        }

        return $this;
    }

    public function removeLikeRecette(LikeRecette $likeRecette): static
    {
        if ($this->likeRecette->removeElement($likeRecette)) {

            if ($likeRecette->getLikeUser() === $this) {
                $likeRecette->setLikeUser(null);
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
            $favori->setFavoriUser($this);
        }

        return $this;
    }

    public function removeFavori(Favori $favori): static
    {
        if ($this->favoris->removeElement($favori)) {

            if ($favori->getFavoriUser() === $this) {
                $favori->setFavoriUser(null);
            }
        }

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
            $planning->setPlanningUser($this);
        }

        return $this;
    }

    public function removePlanning(Planning $planning): static
    {
        if ($this->plannings->removeElement($planning)) {

            if ($planning->getPlanningUser() === $this) {
                $planning->setPlanningUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Liste>
     */
    public function getListes(): Collection
    {
        return $this->listes;
    }

    public function addListe(Liste $liste): static
    {
        if (!$this->listes->contains($liste)) {
            $this->listes->add($liste);
            $liste->setUser($this);
        }

        return $this;
    }

    public function removeListe(Liste $liste): static
    {
        if ($this->listes->removeElement($liste)) {
            // set the owning side to null (unless already changed)
            if ($liste->getUser() === $this) {
                $liste->setUser(null);
            }
        }

        return $this;
    }

    public function getIntId(): ?int
    {
        return $this->intId;
    }

    public function getArrRoles(): array
    {
        return $this->arrRoles;
    }

    public function setArrRoles(array $arrRoles): static
    {
        $this->arrRoles = $arrRoles;

        return $this;
    }

    public function getStrPassword(): ?string
    {
        return $this->strPassword;
    }

    public function setStrPassword(string $strPassword): static
    {
        $this->strPassword = $strPassword;

        return $this;
    }
}