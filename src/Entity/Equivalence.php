<?php

namespace App\Entity;

use App\Repository\EquivalenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: EquivalenceRepository::class)]
#[ORM\Table(name: 'equivalence')]
#[ORM\UniqueConstraint(name: 'UNIQ_NUMERO_DOSSIER', columns: ['numero_dossier'])]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['numeroDossier'], message: 'Ce numéro de dossier existe déjà.')]
class Equivalence
{
    // ====================== ID ======================
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // ====================== IDENTIFIANT UNIQUE ======================
    #[ORM\Column(length: 20, unique: true)]
    #[Assert\NotBlank]
    private ?string $numeroDossier = null;

    // ====================== INFORMATIONS CANDIDAT ======================
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    private string $nom;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    private string $prenom;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Assert\NotBlank]
    private ?\DateTimeImmutable $dateNaissance = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $lieuNaissance = null;

    // ====================== COORDONNÉES ======================
    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    // ====================== NATIONALITÉ & CNI ======================
    #[ORM\ManyToOne(targetEntity: Pays::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Pays $nationalite = null;

    #[ORM\Column(length: 12, nullable: true)]
    #[Assert\Length(exactly: 12)]
    #[Assert\Regex(pattern: '/^\d{12}$/', message: 'Le CNI doit contenir exactement 12 chiffres.')]
    private ?string $cni = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $cniDateDelivrance = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $cniLieuDelivrance = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $cniDateDuplicata = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $cniLieuDuplicata = null;

    // ====================== SITUATION PROFESSIONNELLE ======================
    #[ORM\Column(length: 30, nullable: true)]
    private ?string $emploi = null; // chomeur, prive, fonctionnaire

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $matricule = null;

    // ====================== DIPLÔME & ÉQUIVALENCE ======================
    #[ORM\ManyToOne(targetEntity: Diplome::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    #[Assert\NotBlank]
    private ?Diplome $diplomeReference = null;

    // Champs rétrocompatibilité (remplis automatiquement)
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $diplome = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $universite = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $pays = null;

    // ====================== OBSERVATIONS & DÉCISION ======================
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observation = null;

    #[ORM\Column(length: 50)]
    private string $status = 'draft';

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $decision = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $classement = null;

    #[ORM\ManyToOne(targetEntity: RegleEquivalence::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?RegleEquivalence $regleAppliquee = null;

    // ====================== DOCUMENTS ======================
    #[ORM\OneToMany(mappedBy: 'equivalence', targetEntity: Document::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $documents;

    // ====================== ARRÊTÉ ======================
    #[ORM\OneToOne(mappedBy: 'equivalence', targetEntity: Arrete::class, cascade: ['persist', 'remove'])]
    private ?Arrete $arrete = null;

    // ====================== DATES ======================
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    // ====================== CONSTRUCTEUR ======================
    public function __construct()
    {
        $this->documents = new ArrayCollection();
        $this->status = 'draft';
    }

    // ====================== LIFECYCLE CALLBACKS ======================
    #[ORM\PrePersist]
    public function onCreate(): void
    {
        $this->createdAt = new \DateTimeImmutable();

        if (empty($this->numeroDossier)) {
            // Sécurité : fallback si le contrôleur n'a pas généré le numéro
            $this->numeroDossier = 'EQ-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(6)));
        }
    }

    #[ORM\PreUpdate]
    public function onUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // ====================== GETTERS & SETTERS ======================

    public function getId(): ?int { return $this->id; }

    public function getNumeroDossier(): ?string { return $this->numeroDossier; }
    public function setNumeroDossier(string $numeroDossier): self
    {
        $this->numeroDossier = strtoupper(trim($numeroDossier));
        return $this;
    }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): self
    {
        $this->nom = strtoupper(trim($nom));
        return $this;
    }

    public function getPrenom(): string { return $this->prenom; }
    public function setPrenom(string $prenom): self
    {
        $this->prenom = strtoupper(trim($prenom));
        return $this;
    }

    public function getDateNaissance(): ?\DateTimeImmutable { return $this->dateNaissance; }
    public function setDateNaissance(?\DateTimeImmutable $dateNaissance): self
    {
        $this->dateNaissance = $dateNaissance;
        return $this;
    }

    public function getLieuNaissance(): ?string { return $this->lieuNaissance; }
    public function setLieuNaissance(?string $lieuNaissance): self
    {
        $this->lieuNaissance = strtoupper(trim($lieuNaissance ?? ''));
        return $this;
    }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): self
    {
        $this->email = strtolower(trim($email));
        return $this;
    }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getNationalite(): ?Pays { return $this->nationalite; }
    public function setNationalite(?Pays $nationalite): self
    {
        $this->nationalite = $nationalite;
        return $this;
    }

    public function getCni(): ?string { return $this->cni; }
    public function setCni(?string $cni): self
    {
        $this->cni = $cni ? preg_replace('/\s+/', '', $cni) : null;
        return $this;
    }

    // ====================== GETTERS/SETTERS CNI ======================
public function getCniDateDelivrance(): ?\DateTimeImmutable { return $this->cniDateDelivrance; }
public function setCniDateDelivrance(?\DateTimeImmutable $date): self
{
    $this->cniDateDelivrance = $date;
    return $this;
}

public function getCniLieuDelivrance(): ?string { return $this->cniLieuDelivrance; }
public function setCniLieuDelivrance(?string $lieu): self
{
    $this->cniLieuDelivrance = $lieu ? strtoupper(trim($lieu)) : null;
    return $this;
}

public function getCniDateDuplicata(): ?\DateTimeImmutable { return $this->cniDateDuplicata; }
public function setCniDateDuplicata(?\DateTimeImmutable $date): self
{
    $this->cniDateDuplicata = $date;
    return $this;
}

public function getCniLieuDuplicata(): ?string { return $this->cniLieuDuplicata; }
public function setCniLieuDuplicata(?string $lieu): self
{
    $this->cniLieuDuplicata = $lieu ? strtoupper(trim($lieu)) : null;
    return $this;
}

// ====================== CHAMPS RÉTROCOMPATIBILITÉ ======================
public function getDiplome(): ?string { return $this->diplome; }
public function setDiplome(?string $diplome): self
{
    $this->diplome = $diplome ? strtoupper(trim($diplome)) : null;
    return $this;
}

public function getUniversite(): ?string { return $this->universite; }
public function setUniversite(?string $universite): self
{
    $this->universite = $universite ? strtoupper(trim($universite)) : null;
    return $this;
}

public function getPays(): ?string { return $this->pays; }
public function setPays(?string $pays): self
{
    $this->pays = $pays ? strtoupper(trim($pays)) : null;
    return $this;
}

    public function getEmploi(): ?string { return $this->emploi; }
    public function setEmploi(?string $emploi): self { $this->emploi = $emploi; return $this; }

    public function getMatricule(): ?string { return $this->matricule; }
    public function setMatricule(?string $matricule): self
    {
        $this->matricule = $matricule ? strtoupper(trim($matricule)) : null;
        return $this;
    }

    public function getDiplomeReference(): ?Diplome { return $this->diplomeReference; }
    public function setDiplomeReference(?Diplome $diplomeReference): self
    {
        $this->diplomeReference = $diplomeReference;
        return $this;
    }

    public function getObservation(): ?string { return $this->observation; }
    public function setObservation(?string $observation): self { $this->observation = $observation; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }

    public function getDecision(): ?string { return $this->decision; }
    public function setDecision(?string $decision): self { $this->decision = $decision; return $this; }

    public function getClassement(): ?string { return $this->classement; }
    public function setClassement(?string $classement): self { $this->classement = $classement; return $this; }

    public function getRegleAppliquee(): ?RegleEquivalence { return $this->regleAppliquee; }
    public function setRegleAppliquee(?RegleEquivalence $regle): self { $this->regleAppliquee = $regle; return $this; }

    public function getDocuments(): Collection { return $this->documents; }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setEquivalence($this);
        }
        return $this;
    }

    public function getArrete(): ?Arrete { return $this->arrete; }
    public function setArrete(?Arrete $arrete): self
    {
        $this->arrete = $arrete;
        if ($arrete && $arrete->getEquivalence() !== $this) {
            $arrete->setEquivalence($this);
        }
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    // ====================== HELPERS ======================
    public function getNomComplet(): string
    {
        return trim($this->prenom . ' ' . $this->nom);
    }

    public function getDiplomeTitre(): string
    {
        return $this->diplomeReference?->getTitre() ?? $this->diplome ?? 'N/A';
    }

    public function getEtablissementNom(): ?string
    {
        return $this->diplomeReference?->getEtablissement()?->getNom() ?? $this->universite;
    }

    public function __toString(): string
    {
        return $this->numeroDossier ?? 'Nouveau dossier';
    }
}
