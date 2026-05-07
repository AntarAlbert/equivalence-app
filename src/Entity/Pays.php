<?php
// src/Entity/Pays.php

namespace App\Entity;

use App\Repository\PaysRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PaysRepository::class)]
#[ORM\Table(name: 'pays')]
#[ORM\UniqueConstraint(name: 'uniq_pays_alpha2', columns: ['alpha2'])]
#[ORM\UniqueConstraint(name: 'uniq_pays_alpha3', columns: ['alpha3'])]
#[ORM\UniqueConstraint(name: 'uniq_pays_code', columns: ['code'])]
class Pays
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'smallint', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\Positive]
    private int $code;

    #[ORM\Column(length: 2)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 2)]
    private string $alpha2;

    #[ORM\Column(length: 3)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 3)]
    private string $alpha3;

    #[ORM\Column(length: 45)]
    #[Assert\NotBlank]
    private string $nomEnGb;

    #[ORM\Column(length: 45)]
    #[Assert\NotBlank]
    private string $nomFrFr;

    #[ORM\OneToMany(mappedBy: 'pays', targetEntity: Etablissement::class, cascade: ['persist'])]
    private Collection $etablissements;

    public function __construct()
    {
        $this->etablissements = new ArrayCollection();
    }

    // Getters / Setters
    public function getId(): ?int { return $this->id; }
    public function getCode(): int { return $this->code; }
    public function setCode(int $code): self { $this->code = $code; return $this; }
    public function getAlpha2(): string { return $this->alpha2; }
    public function setAlpha2(string $alpha2): self { $this->alpha2 = strtoupper(trim($alpha2)); return $this; }
    public function getAlpha3(): string { return $this->alpha3; }
    public function setAlpha3(string $alpha3): self { $this->alpha3 = strtoupper(trim($alpha3)); return $this; }
    public function getNomEnGb(): string { return $this->nomEnGb; }
    public function setNomEnGb(string $nomEnGb): self { $this->nomEnGb = trim($nomEnGb); return $this; }
    public function getNomFrFr(): string { return $this->nomFrFr; }
    public function setNomFrFr(string $nomFrFr): self { $this->nomFrFr = trim($nomFrFr); return $this; }

    /** @return Collection<int, Etablissement> */
    public function getEtablissements(): Collection { return $this->etablissements; }
    public function addEtablissement(Etablissement $etablissement): self
    {
        if (!$this->etablissements->contains($etablissement)) {
            $this->etablissements[] = $etablissement;
            $etablissement->setPays($this);
        }
        return $this;
    }
    public function removeEtablissement(Etablissement $etablissement): self
    {
        if ($this->etablissements->removeElement($etablissement) && $etablissement->getPays() === $this) {
            $etablissement->setPays(null);
        }
        return $this;
    }

    public function getDisplayName(): string { return sprintf('%s (%s)', $this->nomFrFr, $this->alpha2); }
    public function __toString(): string { return $this->getDisplayName(); }
}
