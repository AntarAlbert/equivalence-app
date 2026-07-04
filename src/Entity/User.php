<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    // =========================================================
    // ID
    // =========================================================

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // =========================================================
    // AUTHENTIFICATION
    // =========================================================

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    // =========================================================
    // VALIDATION COMPTE
    // =========================================================

    #[ORM\Column(type: 'boolean')]
    private bool $isVerified = false;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $verifiedAt = null;

    // =========================================================
    // INFORMATIONS PERSONNELLES
    // =========================================================

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateNaissance = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lieuNaissance = null;

    #[ORM\ManyToOne(targetEntity: Pays::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Pays $nationalite = null;

    // =========================================================
    // CNI
    // =========================================================

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $cni = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $cniDateDelivrance = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cniLieuDelivrance = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $cniDateDuplicata = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cniLieuDuplicata = null;

    // =========================================================
    // ETABLISSEMENT
    // =========================================================

    #[ORM\ManyToOne(targetEntity: Etablissement::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Etablissement $etablissement = null;

    // =========================================================
    // TIMESTAMPS
    // =========================================================

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

      // ========== AUTRES CHAMPS EXISTANTS ==========
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fullName = null;  // facultatif, peut être déprécié

    // =========================================================
    // CONSTRUCTEUR
    // =========================================================

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->roles = [];
    }

    // =========================================================
    // LIFECYCLE
    // =========================================================

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // =========================================================
    // GETTERS / SETTERS
    // =========================================================

    public function getId(): ?int
    {
        return $this->id;
    }

    // =========================================================
    // EMAIL
    // =========================================================

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = strtolower(trim($email));

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    // =========================================================
    // ROLES
    // =========================================================

   public function getRoles(): array
{
    $roles = $this->roles;

    // rôle minimal Symfony
    $roles[] = 'ROLE_USER';

    return array_unique($roles);
}

    public function setRoles(array $roles): self
    {
        $this->roles = array_unique($roles);

        return $this;
    }

    // =========================================================
    // PASSWORD
    // =========================================================

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    // =========================================================
    // VERIFIED
    // =========================================================

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getVerifiedAt(): ?\DateTimeImmutable
    {
        return $this->verifiedAt;
    }

    public function setVerifiedAt(?\DateTimeImmutable $verifiedAt): self
    {
        $this->verifiedAt = $verifiedAt;

        return $this;
    }

    // =========================================================
    // NOM / PRENOM
    // =========================================================

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }
     // ========== AUTRES ==========
    public function getFullName(): ?string { return $this->fullName; }
    public function setFullName(?string $fullName): self
    {
        $this->fullName = $fullName ? trim($fullName) : null;
        return $this;
    }


    // =========================================================
    // NAISSANCE
    // =========================================================

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(?\DateTimeInterface $dateNaissance): self
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    public function getLieuNaissance(): ?string
    {
        return $this->lieuNaissance;
    }

    public function setLieuNaissance(?string $lieuNaissance): self
    {
        $this->lieuNaissance = $lieuNaissance;

        return $this;
    }

    // =========================================================
    // NATIONALITE
    // =========================================================

    public function getNationalite(): ?Pays
    {
        return $this->nationalite;
    }

    public function setNationalite(?Pays $nationalite): self
    {
        $this->nationalite = $nationalite;

        return $this;
    }

    // =========================================================
    // CNI
    // =========================================================

    public function getCni(): ?string
    {
        return $this->cni;
    }

    public function setCni(?string $cni): self
    {
        $this->cni = $cni;

        return $this;
    }

    public function getCniDateDelivrance(): ?\DateTimeInterface
    {
        return $this->cniDateDelivrance;
    }

    public function setCniDateDelivrance(?\DateTimeInterface $date): self
    {
        $this->cniDateDelivrance = $date;

        return $this;
    }

    public function getCniLieuDelivrance(): ?string
    {
        return $this->cniLieuDelivrance;
    }

    public function setCniLieuDelivrance(?string $lieu): self
    {
        $this->cniLieuDelivrance = $lieu;

        return $this;
    }

    public function getCniDateDuplicata(): ?\DateTimeInterface
    {
        return $this->cniDateDuplicata;
    }

    public function setCniDateDuplicata(?\DateTimeInterface $date): self
    {
        $this->cniDateDuplicata = $date;

        return $this;
    }

    public function getCniLieuDuplicata(): ?string
    {
        return $this->cniLieuDuplicata;
    }

    public function setCniLieuDuplicata(?string $lieu): self
    {
        $this->cniLieuDuplicata = $lieu;

        return $this;
    }

    // =========================================================
    // ETABLISSEMENT
    // =========================================================

    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }

    public function setEtablissement(?Etablissement $etablissement): self
    {
        $this->etablissement = $etablissement;

        return $this;
    }

    // =========================================================
    // TIMESTAMPS
    // =========================================================

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // =========================================================
    // HELPERS ROLES
    // =========================================================

    public function isAgent(): bool
    {
        return in_array('ROLE_AGENT', $this->getRoles(), true);
    }

    public function isCommission(): bool
    {
        return in_array('ROLE_COMMISSION', $this->getRoles(), true);
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->getRoles(), true);
    }

    public function isCandidat(): bool
    {
        return in_array('ROLE_CANDIDAT', $this->getRoles(), true);
    }

    // =========================================================
    // EQUALITY
    // =========================================================

    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }

        return
            $this->id === $user->id
            && $this->email === $user->email
            && $this->password === $user->password;
    }
}
