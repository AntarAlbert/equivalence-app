<?php
// src/Entity/Considerant.php

namespace App\Entity;

use App\Repository\ConsiderantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ConsiderantRepository::class)]
#[ORM\Table(name: 'considerant')]
class Considerant
{
    public const TYPES = [
        'Constitution' => 'constitution',
        'Loi' => 'loi',
        'Décret' => 'decret',
        'Arrêté' => 'arrete',
        'Circulaire' => 'circulaire',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    private string $type;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private string $reference;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $portant;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $extrait = null;

    #[ORM\Column]
    private int $ordre = 0;

    /**
     * @var Collection<int, ArreteConsiderant>
     */
    #[ORM\OneToMany(
        mappedBy: 'considerant',
        targetEntity: ArreteConsiderant::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $arreteConsiderants;

    public function __construct()
    {
        $this->arreteConsiderants = new ArrayCollection();
    }

    // ────────────────────────────────────────────────────────────────
    // Getters / Setters
    // ────────────────────────────────────────────────────────────────

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;
        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(?\DateTimeImmutable $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getPortant(): string
    {
        return $this->portant;
    }

    public function setPortant(string $portant): self
    {
        $this->portant = $portant;
        return $this;
    }

    public function getExtrait(): ?string
    {
        return $this->extrait;
    }

    public function setExtrait(?string $extrait): self
    {
        $this->extrait = $extrait;
        return $this;
    }

    public function getOrdre(): int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): self
    {
        $this->ordre = $ordre;
        return $this;
    }

    /**
     * @return Collection<int, ArreteConsiderant>
     */
    public function getArreteConsiderants(): Collection
    {
        return $this->arreteConsiderants;
    }

    public function addArreteConsiderant(ArreteConsiderant $arreteConsiderant): self
    {
        if (!$this->arreteConsiderants->contains($arreteConsiderant)) {
            $this->arreteConsiderants[] = $arreteConsiderant;
            $arreteConsiderant->setConsiderant($this);
        }
        return $this;
    }

    public function removeArreteConsiderant(ArreteConsiderant $arreteConsiderant): self
    {
        if ($this->arreteConsiderants->removeElement($arreteConsiderant) && $arreteConsiderant->getConsiderant() === $this) {
            $arreteConsiderant->setConsiderant(null);
        }
        return $this;
    }

    // ────────────────────────────────────────────────────────────────
    // Helpers d'affichage
    // ────────────────────────────────────────────────────────────────

    public function getDisplayName(): string
    {
        return sprintf(
            '%s %s (%s)',
            ucfirst($this->type),
            $this->reference,
            $this->date ? $this->date->format('d/m/Y') : 'date inconnue'
        );
    }

    public function getFormattedDate(): string
    {
        return $this->date ? $this->date->format('d/m/Y') : '';
    }

    public function __toString(): string
    {
        return $this->getDisplayName();
    }
}
