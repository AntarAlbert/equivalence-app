<?php
// src/Entity/Equivalence.php

namespace App\Entity;

use App\Repository\EquivalenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EquivalenceRepository::class)]
#[ORM\Table(name: 'equivalence')]
#[ORM\HasLifecycleCallbacks]
class Equivalence
{
    // ====================== ID ======================
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // ====================== ANCIENNES COLONNES ======================
    #[ORM\Column(length: 50, unique: true)]
    private string $numeroDossier;

    #[ORM\Column(length: 100)]
    private string $nom;

    #[ORM\Column(length: 100)]
    private string $prenom;

   #[ORM\Column(length: 255, nullable: true)]
    private ?string $diplome = null;  // texte libre (rétrocompatibilité)

   #[ORM\Column(length: 255, nullable: true)]
    private ?string $universite = null; // texte libre (rétrocompatibilité)

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $pays = null;      // texte libre (rétrocompatibilité)

    // ====================== NOUVELLES COLONNES ======================
    #[ORM\ManyToOne(targetEntity: Pays::class)]
    #[ORM\JoinColumn(name: 'nationalite_id', referencedColumnName: 'id', nullable: true)]
    private ?Pays $nationalite = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateNaissance = null;

    // ====================== RELATIONS ======================
    #[ORM\ManyToOne(targetEntity: Diplome::class, inversedBy: 'equivalences')]
    #[ORM\JoinColumn(name: 'diplome_reference_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Diplome $diplomeReference = null;

    #[ORM\ManyToOne(targetEntity: RegleEquivalence::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?RegleEquivalence $regleAppliquee = null;

    // Ajouter cette propriété (remplace toute ancienne propriété "arrete")
#[ORM\OneToOne(mappedBy: 'equivalence', targetEntity: Arrete::class, cascade: ['persist', 'remove'])]
private ?Arrete $arrete = null;

    // ====================== WORKFLOW ======================
    #[ORM\Column(length: 50)]
    private string $status = 'draft';

    #[ORM\Column(nullable: true)]
    private ?string $decision = null;

    #[ORM\Column(nullable: true)]
    private ?string $classement = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observation = null;

    // ====================== DOCUMENTS ======================
    #[ORM\OneToMany(mappedBy: 'equivalence', targetEntity: Document::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $documents;

    // ====================== OTP & EMAIL ======================
    #[ORM\Column(length: 180, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 6, nullable: true)]
    private ?string $confirmationCode = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $codeRequestedAt = null;

    // ====================== DATES ======================
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    // src/Entity/Equivalence.php

    #[ORM\Column(length: 12, nullable: true)]
    private ?string $cni = null;

#[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
private ?\DateTimeImmutable $cniDateDelivrance = null;

#[ORM\Column(length: 255, nullable: true)]
private ?string $cniLieuDelivrance = null;

#[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
private ?\DateTimeImmutable $cniDateDuplicata = null;

#[ORM\Column(length: 255, nullable: true)]
private ?string $cniLieuDuplicata = null;

// src/Entity/Equivalence.php
#[ORM\ManyToOne(targetEntity: User::class)]
#[ORM\JoinColumn(nullable: true)]
private ?User $user = null;

public function getUser(): ?User { return $this->user; }
public function setUser(?User $user): self { $this->user = $user; return $this; }


public function setNom(string $nom): self
{
    $this->nom = strtoupper(trim($nom));
    return $this;
}

public function setPrenom(string $prenom): self
{
    $this->prenom = strtoupper(trim($prenom));
    return $this;
}

// Pour l’ancien champ `diplome` (si vous le conservez)
public function setDiplome(string $diplome): self
{
    $this->diplome = strtoupper(trim($diplome));
    return $this;
}
public function getCniDateDelivrance(): ?\DateTimeImmutable { return $this->cniDateDelivrance; }
public function setCniDateDelivrance(?\DateTimeImmutable $date): self { $this->cniDateDelivrance = $date; return $this; }
public function getCniLieuDelivrance(): ?string { return $this->cniLieuDelivrance; }
public function setCniLieuDelivrance(?string $lieu): self { $this->cniLieuDelivrance = $lieu; return $this; }
public function getCniDateDuplicata(): ?\DateTimeImmutable { return $this->cniDateDuplicata; }
public function setCniDateDuplicata(?\DateTimeImmutable $date): self { $this->cniDateDuplicata = $date; return $this; }
public function getCniLieuDuplicata(): ?string { return $this->cniLieuDuplicata; }
public function setCniLieuDuplicata(?string $lieu): self { $this->cniLieuDuplicata = $lieu; return $this; }

    // ====================== CONSTRUCTEUR ======================
    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onCreate(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        // La génération du numéro est maintenant faite dans le contrôleur
        if (empty($this->numeroDossier)) {
            // Fallback (ne devrait pas arriver)
            $this->numeroDossier = 'EQ-' . (new \DateTimeImmutable())->format('YmdHis') . '-' . bin2hex(random_bytes(4));
        }
        $this->captureActiveRule();
    }


    #[ORM\PreUpdate]
    public function onUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    private function captureActiveRule(): void
    {
        if ($this->diplomeReference && $this->regleAppliquee === null) {
            $this->regleAppliquee = $this->diplomeReference->getRegleActive();
        }
    }

    // ====================== GETTERS / SETTERS ======================

    public function getCni(): ?string { return $this->cni; }
    public function setCni(?string $cni): self { $this->cni = $cni; return $this; }

    public function getId(): ?int { return $this->id; }

    public function getNumeroDossier(): ?string { return $this->numeroDossier; }
    public function setNumeroDossier(string $numeroDossier): self { $this->numeroDossier = $numeroDossier; return $this; }
    public function getNom(): ?string { return $this->nom; }
    public function getPrenom(): ?string { return $this->prenom; }
    // Anciens champs (compatibilité)
    public function getDiplome(): ?string { return $this->diplome; }

    public function getUniversite(): ?string { return $this->universite; }
    public function setUniversite(string $universite): self { $this->universite = $universite; return $this; }

    public function getPays(): ?string { return $this->pays; }
    public function setPays(string $pays): self { $this->pays = $pays; return $this; }

    // Nouveaux champs
    public function getNationalite(): ?Pays { return $this->nationalite; }
    public function setNationalite(?Pays $nationalite): self { $this->nationalite = $nationalite; return $this; }

    public function getDateNaissance(): ?\DateTimeImmutable { return $this->dateNaissance; }
    public function setDateNaissance(?\DateTimeImmutable $dateNaissance): self { $this->dateNaissance = $dateNaissance; return $this; }

    // Relations
    public function getDiplomeReference(): ?Diplome { return $this->diplomeReference; }
    public function setDiplomeReference(?Diplome $diplomeReference): self
    {
        $this->diplomeReference = $diplomeReference;
        if ($diplomeReference && $this->regleAppliquee === null) {
            $this->captureActiveRule();
        }
        return $this;
    }

    public function getRegleAppliquee(): ?RegleEquivalence { return $this->regleAppliquee; }
    public function setRegleAppliquee(?RegleEquivalence $regleAppliquee): self { $this->regleAppliquee = $regleAppliquee; return $this; }

    // Workflow
    public function getStatus(): ?string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function getDecision(): ?string { return $this->decision; }
    public function setDecision(?string $decision): self { $this->decision = $decision; return $this; }
    public function getClassement(): ?string { return $this->classement; }
    public function setClassement(?string $classement): self { $this->classement = $classement; return $this; }
    public function getObservation(): ?string { return $this->observation; }
    public function setObservation(?string $observation): self { $this->observation = $observation; return $this; }

    // Documents
    public function getDocuments(): Collection { return $this->documents; }
    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->setEquivalence($this);
        }
        return $this;
    }
    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document) && $document->getEquivalence() === $this) {
            $document->setEquivalence(null);
        }
        return $this;
    }

    // OTP / Email
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): self { $this->email = $email; return $this; }
    public function getConfirmationCode(): ?string { return $this->confirmationCode; }
    public function setConfirmationCode(?string $code): self { $this->confirmationCode = $code; return $this; }
    public function getCodeRequestedAt(): ?\DateTimeImmutable { return $this->codeRequestedAt; }
    public function setCodeRequestedAt(?\DateTimeImmutable $at): self { $this->codeRequestedAt = $at; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    // ====================== HELPERS ======================
    public function getPaysEtablissement(): ?string
    {
        return $this->diplomeReference?->getEtablissement()?->getPays();
    }

    public function getEtablissementNom(): ?string
    {
        return $this->diplomeReference?->getEtablissement()?->getNom() ?? $this->universite;
    }

    public function getDiplomeTitre(): string
    {
        return $this->diplomeReference?->getTitre() ?? $this->diplome;
    }

    public function getRegleEffective(): ?RegleEquivalence
    {
        return $this->regleAppliquee ?? $this->diplomeReference?->getRegleActive();
    }

    public function getCategorie(): ?string
    {
        return $this->getRegleEffective()?->getCategorie();
    }

    public function getBonification(): ?int
    {
        return $this->getRegleEffective()?->getBonification();
    }

    public function getCadre(): ?string
    {
        return $this->getRegleEffective()?->getCadre()?->value;
    }

    public function getEchelle(): ?string
    {
        return $this->getRegleEffective()?->getEchelle()?->value;
    }

    public function getClassementComplet(): ?string
    {
        return $this->getRegleEffective()?->getClassementComplet();
    }

    public function getNomComplet(): string
    {
        return trim($this->prenom . ' ' . $this->nom);
    }

    public function __toString(): string
    {
        return $this->numeroDossier . ' - ' . $this->getNomComplet();
    }

/**
 * Get the value of arrete
 */
public function getArrete(): ?Arrete
{
return $this->arrete;
}

/**
 * Set the value of arrete
 */
public function setArrete(?Arrete $arrete): self
{
    $this->arrete = $arrete;
    // synchronisation bidirectionnelle
    if ($arrete && $arrete->getEquivalence() !== $this) {
        $arrete->setEquivalence($this);
    }
    return $this;
}
}
