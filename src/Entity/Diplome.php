<?php

namespace App\Entity;

use App\Repository\DiplomeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DiplomeRepository::class)]
#[ORM\Table(name: 'diplome')]
#[ORM\HasLifecycleCallbacks]
class Diplome
{
    // ========== CONSTANTES DE STATUT ==========
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUSES = [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED];

    // ========== PROPRIÉTÉS PRINCIPALES ==========
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre du diplôme est obligatoire')]
    private ?string $titre = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $domaine = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $niveau = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $duree = null;

    // ========== RELATIONS ==========
    #[ORM\ManyToOne(targetEntity: Etablissement::class, inversedBy: 'diplomes')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Etablissement $etablissement = null;

    #[ORM\OneToMany(mappedBy: 'diplomeReference', targetEntity: Equivalence::class)]
    private Collection $equivalences;

    #[ORM\OneToMany(mappedBy: 'diplome', targetEntity: RegleEquivalence::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $reglesEquivalence;

    // ========== VALIDATION & AUDIT ==========
    #[ORM\Column(length: 20, options: ['default' => 'pending'])]
    #[Assert\Choice(choices: self::STATUSES)]
    private string $validationStatus = self::STATUS_PENDING;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $proposedBy = null;

    #[ORM\ManyToOne(targetEntity: Etablissement::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Etablissement $etablissementSource = null;

    // Audit trail
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $createdBy = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $updatedBy = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $approvedBy = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $approvedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $rejectedBy = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $rejectedAt = null;

    // Timestamps
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    // ========== CONSTRUCTEUR ==========
    public function __construct()
    {
        $this->equivalences = new ArrayCollection();
        $this->reglesEquivalence = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

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

    // ========== GETTERS / SETTERS ==========
    public function getId(): ?int { return $this->id; }

    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $titre): self { $this->titre = trim($titre); return $this; }

    public function getDomaine(): ?string { return $this->domaine; }
    public function setDomaine(?string $domaine): self { $this->domaine = $domaine ? trim($domaine) : null; return $this; }

    public function getNiveau(): ?string { return $this->niveau; }
    public function setNiveau(?string $niveau): self { $this->niveau = $niveau ? trim($niveau) : null; return $this; }

    public function getDuree(): ?string { return $this->duree; }
    public function setDuree(?string $duree): self { $this->duree = $duree ? trim($duree) : null; return $this; }

    // Relations
    public function getEtablissement(): ?Etablissement { return $this->etablissement; }
    public function setEtablissement(?Etablissement $etablissement): self { $this->etablissement = $etablissement; return $this; }

    /**
     * @return Collection<int, Equivalence>
     */
    public function getEquivalences(): Collection { return $this->equivalences; }
    public function addEquivalence(Equivalence $equivalence): self
    {
        if (!$this->equivalences->contains($equivalence)) {
            $this->equivalences[] = $equivalence;
            $equivalence->setDiplomeReference($this);
        }
        return $this;
    }
    public function removeEquivalence(Equivalence $equivalence): self
    {
        if ($this->equivalences->removeElement($equivalence) && $equivalence->getDiplomeReference() === $this) {
            $equivalence->setDiplomeReference(null);
        }
        return $this;
    }

    /**
     * @return Collection<int, RegleEquivalence>
     */
    public function getReglesEquivalence(): Collection { return $this->reglesEquivalence; }
    public function addRegleEquivalence(RegleEquivalence $regle): self
    {
        if (!$this->reglesEquivalence->contains($regle)) {
            $this->reglesEquivalence[] = $regle;
            $regle->setDiplome($this);
        }
        return $this;
    }
    public function removeRegleEquivalence(RegleEquivalence $regle): self
    {
        if ($this->reglesEquivalence->removeElement($regle) && $regle->getDiplome() === $this) {
            $regle->setDiplome(null);
        }
        return $this;
    }

    // Validation & audit
    public function getValidationStatus(): string { return $this->validationStatus; }
    public function setValidationStatus(string $validationStatus): self
    {
        if (!in_array($validationStatus, self::STATUSES)) {
            throw new \InvalidArgumentException('Statut de validation invalide');
        }
        $this->validationStatus = $validationStatus;
        return $this;
    }

    public function getProposedBy(): ?User { return $this->proposedBy; }
    public function setProposedBy(?User $proposedBy): self { $this->proposedBy = $proposedBy; return $this; }

    public function getEtablissementSource(): ?Etablissement { return $this->etablissementSource; }
    public function setEtablissementSource(?Etablissement $etablissementSource): self { $this->etablissementSource = $etablissementSource; return $this; }

    public function getCreatedBy(): ?User { return $this->createdBy; }
    public function setCreatedBy(?User $createdBy): self { $this->createdBy = $createdBy; return $this; }

    public function getUpdatedBy(): ?User { return $this->updatedBy; }
    public function setUpdatedBy(?User $updatedBy): self { $this->updatedBy = $updatedBy; return $this; }

    public function getApprovedBy(): ?User { return $this->approvedBy; }
    public function setApprovedBy(?User $approvedBy): self { $this->approvedBy = $approvedBy; return $this; }

    public function getApprovedAt(): ?\DateTimeImmutable { return $this->approvedAt; }
    public function setApprovedAt(?\DateTimeImmutable $approvedAt): self { $this->approvedAt = $approvedAt; return $this; }

    public function getRejectedBy(): ?User { return $this->rejectedBy; }
    public function setRejectedBy(?User $rejectedBy): self { $this->rejectedBy = $rejectedBy; return $this; }

    public function getRejectedAt(): ?\DateTimeImmutable { return $this->rejectedAt; }
    public function setRejectedAt(?\DateTimeImmutable $rejectedAt): self { $this->rejectedAt = $rejectedAt; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self { $this->updatedAt = $updatedAt; return $this; }

    // ========== MÉTHODE CRITIQUE : RÈGLE ACTIVE ==========
    /**
     * Retourne la règle d'équivalence active pour ce diplôme à la date du jour.
     * Une règle est active si :
     * - actif = true
     * - dateDebut <= aujourd'hui
     * - dateFin est null ou >= aujourd'hui
     */
    public function getRegleActive(): ?RegleEquivalence
    {
        $now = new \DateTimeImmutable();
        $activeRule = null;

        foreach ($this->reglesEquivalence as $rule) {
            if (!$rule->isActif()) {
                continue;
            }
            $dateDebut = $rule->getDateDebut();
            $dateFin = $rule->getDateFin();

            if ($dateDebut <= $now && ($dateFin === null || $dateFin >= $now)) {
                // En cas de plusieurs règles valides, on prend la plus récente (ID le plus élevé)
                if ($activeRule === null || $rule->getId() > $activeRule->getId()) {
                    $activeRule = $rule;
                }
            }
        }

        return $activeRule;
    }

    // ========== MÉTHODES UTILITAIRES ==========
    public function isPending(): bool { return $this->validationStatus === self::STATUS_PENDING; }
    public function isApproved(): bool { return $this->validationStatus === self::STATUS_APPROVED; }
    public function isRejected(): bool { return $this->validationStatus === self::STATUS_REJECTED; }

    public function getDisplayName(): string
    {
        $name = $this->titre ?? 'Sans titre';
        if ($this->etablissement) {
            $name .= ' (' . $this->etablissement->getNom() . ')';
        }
        return $name;
    }

    public function __toString(): string
    {
        return $this->getDisplayName();
    }
}
