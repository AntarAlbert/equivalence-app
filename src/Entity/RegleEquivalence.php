<?php
// src/Entity/RegleEquivalence.php

namespace App\Entity;

use App\Enum\Cadre;
use App\Enum\Echelle;
use App\Repository\RegleEquivalenceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RegleEquivalenceRepository::class)]
#[ORM\Table(name: 'regle_equivalence')]
#[ORM\HasLifecycleCallbacks]
class RegleEquivalence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(
        targetEntity: Diplome::class,
        inversedBy: 'reglesEquivalence'
    )]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Diplome $diplome = null;

    #[ORM\Column(enumType: Cadre::class)]
    private ?Cadre $cadre = null;

    #[ORM\Column(enumType: Echelle::class)]
    private ?Echelle $echelle = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: 'La catégorie est obligatoire.')]
    private ?string $categorie = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero(
        message: 'La bonification doit être positive.'
    )]
    private int $bonification = 0;

    // =====================================================
    // DATE DEBUT
    // =====================================================

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull(
        message: 'La date de début est obligatoire.'
    )]
    private ?\DateTimeImmutable $dateDebut = null;

    // =====================================================
    // DATE FIN
    // =====================================================

    #[ORM\Column(
        type: Types::DATE_IMMUTABLE,
        nullable: true
    )]
    private ?\DateTimeImmutable $dateFin = null;

    #[ORM\Column]
    private bool $actif = true;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $texteReference = null;

    #[ORM\Column(
        type: Types::DATETIME_IMMUTABLE,
        nullable: true
    )]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(
        type: Types::DATETIME_IMMUTABLE,
        nullable: true
    )]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();

        // Valeur par défaut utile
        $this->dateDebut = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // =====================================================
    // SOFT DELETE
    // =====================================================

    public function softDelete(): self
{
    $this->deletedAt = new \DateTimeImmutable();

    return $this;
}

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    // =====================================================
    // GETTERS / SETTERS
    // =====================================================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDiplome(): ?Diplome
    {
        return $this->diplome;
    }

    public function setDiplome(?Diplome $diplome): self
    {
        $this->diplome = $diplome;

        return $this;
    }

    public function getCadre(): ?Cadre
    {
        return $this->cadre;
    }

    public function setCadre(?Cadre $cadre): self
    {
        $this->cadre = $cadre;

        return $this;
    }

    public function getEchelle(): ?Echelle
    {
        return $this->echelle;
    }

    public function setEchelle(?Echelle $echelle): self
    {
        $this->echelle = $echelle;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): self
    {
        $this->categorie =
            $categorie
                ? strtoupper(trim($categorie))
                : null;

        return $this;
    }

    public function getBonification(): int
    {
        return $this->bonification;
    }

    public function setBonification(int $bonification): self
    {
        $this->bonification = max(0, $bonification);

        return $this;
    }

    public function getDateDebut(): ?\DateTimeImmutable
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeImmutable $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeImmutable
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeImmutable $dateFin): self
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function isActif(): bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    public function getTexteReference(): ?string
    {
        return $this->texteReference;
    }

    public function setTexteReference(?string $texteReference): self
    {
        $this->texteReference = $texteReference;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(
        \DateTimeImmutable $createdAt
    ): self {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(
        ?\DateTimeImmutable $updatedAt
    ): self {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    // =====================================================
    // LOGIQUE
    // =====================================================

    public function isValideA(
        \DateTimeInterface $date
    ): bool {

        if (!$this->actif) {
            return false;
        }

        if (
            $this->dateDebut &&
            $date < $this->dateDebut
        ) {
            return false;
        }

        if (
            $this->dateFin &&
            $date > $this->dateFin
        ) {
            return false;
        }

        return true;
    }

    public function getClassementComplet(): string
    {
        return sprintf(
            'CATEGORIE %s +%02d an%s',
            $this->categorie,
            $this->bonification,
            $this->bonification > 1 ? 's' : ''
        );
    }

    public function __toString(): string
    {
        return $this->getClassementComplet();
    }

    //---------------------------------------------------------------------
 public function restore(): self
{
    $this->deletedAt = null;

    return $this;
}

}
