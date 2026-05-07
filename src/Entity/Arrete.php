<?php
// src/Entity/Arrete.php

namespace App\Entity;

use App\Repository\ArreteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArreteRepository::class)]
#[ORM\Table(name: 'arrete')]
class Arrete
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank]
    private string $numeroArrete;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    private \DateTimeImmutable $dateArrete;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $titre;

   #[ORM\OneToOne(inversedBy: 'arrete')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Equivalence $equivalence = null;

    #[ORM\OneToMany(
        mappedBy: 'arrete',
        targetEntity: ArreteConsiderant::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $arreteConsiderants;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $articleDispositif = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->arreteConsiderants = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    // ────────────────────────────────────────────────────────────────
    // Getters / Setters de base
    // ────────────────────────────────────────────────────────────────

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroArrete(): string
    {
        return $this->numeroArrete;
    }

    public function setNumeroArrete(string $numeroArrete): self
    {
        $this->numeroArrete = $numeroArrete;
        return $this;
    }

    public function getDateArrete(): \DateTimeImmutable
    {
        return $this->dateArrete;
    }

    public function setDateArrete(\DateTimeImmutable $dateArrete): self
    {
        $this->dateArrete = $dateArrete;
        return $this;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getEquivalence(): ?Equivalence
    {
        return $this->equivalence;
    }

    public function setEquivalence(?Equivalence $equivalence): self
    {
        $this->equivalence = $equivalence;
        if ($equivalence && $equivalence->getArrete() !== $this) {
            $equivalence->setArrete($this);
        }
        return $this;
    }

    public function getArticleDispositif(): ?string
    {
        return $this->articleDispositif;
    }

    public function setArticleDispositif(?string $articleDispositif): self
    {
        $this->articleDispositif = $articleDispositif;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // ────────────────────────────────────────────────────────────────
    // Gestion de la collection ArreteConsiderant
    // ────────────────────────────────────────────────────────────────

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
            $arreteConsiderant->setArrete($this);
        }
        return $this;
    }

    public function removeArreteConsiderant(ArreteConsiderant $arreteConsiderant): self
    {
        if ($this->arreteConsiderants->removeElement($arreteConsiderant) && $arreteConsiderant->getArrete() === $this) {
            $arreteConsiderant->setArrete(null);
        }
        return $this;
    }

    // ────────────────────────────────────────────────────────────────
    // Helper : récupérer les considérants triés par ordre
    // ────────────────────────────────────────────────────────────────

    /**
     * @return Considerant[]
     */
    public function getConsiderants(): array
    {
        $items = $this->arreteConsiderants->toArray();
        usort($items, fn(ArreteConsiderant $a, ArreteConsiderant $b) => $a->getOrdre() <=> $b->getOrdre());
        return array_map(fn(ArreteConsiderant $ac) => $ac->getConsiderant(), $items);
    }
}
