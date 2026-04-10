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

    public function __construct()
    {
        $this->regimes = new ArrayCollection();
        $this->likeRecette = new ArrayCollection();
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
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->strEmail;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->arrRoles;
        // guarantee every user at least has ROLE_USER
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
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
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
            // set the owning side to null (unless already changed)
            if ($likeRecette->getLikeUser() === $this) {
                $likeRecette->setLikeUser(null);
            }
        }

        return $this;
    }
}