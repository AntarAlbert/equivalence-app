<?php
// src/Entity/Etablissement.php

namespace App\Entity;

use App\Repository\EtablissementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EtablissementRepository::class)]
#[ORM\Table(name: 'etablissement')]
#[ORM\HasLifecycleCallbacks]
class Etablissement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    #[Assert\Length(max: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Assert\Length(max: 150)]
    private ?string $ville = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $type = null;

    #[ORM\ManyToOne(targetEntity: Pays::class, inversedBy: 'etablissements')]
    #[ORM\JoinColumn(name: 'pays_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Pays $pays = null;

    #[ORM\OneToMany(
        targetEntity: Diplome::class,
        mappedBy: 'etablissement',
        cascade: ['persist'],
        orphanRemoval: false
    )]
    private Collection $diplomes;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;


    #[ORM\OneToMany(mappedBy: 'etablissement', targetEntity: User::class)]
    private Collection $users;

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function __construct()
    {
        $this->diplomes = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
         $this->users = new ArrayCollection();
    }

    #[ORM\PreUpdate]
    public function onUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom ? strtoupper(trim($nom)) : null;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): self
    {
        $this->ville = $ville ? trim($ville) : null;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type ? trim($type) : null;

        return $this;
    }

    public function getPays(): ?Pays
    {
        return $this->pays;
    }

    public function setPays(?Pays $pays): self
    {
        $this->pays = $pays;

        return $this;
    }

    /**
     * @return Collection<int, Diplome>
     */
    public function getDiplomes(): Collection
    {
        return $this->diplomes;
    }

    public function addDiplome(Diplome $diplome): self
    {
        if (!$this->diplomes->contains($diplome)) {
            $this->diplomes[] = $diplome;
            $diplome->setEtablissement($this);
        }

        return $this;
    }

    public function removeDiplome(Diplome $diplome): self
    {
        if ($this->diplomes->removeElement($diplome)) {
            if ($diplome->getEtablissement() === $this) {
                $diplome->setEtablissement(null);
            }
        }

        return $this;
    }

    public function getPaysNom(): ?string
    {
        return $this->pays?->getNomFrFr();
    }

    public function getDisplayName(): string
    {
        $nom = $this->nom ?? '';

        if ($this->pays) {
            $nom .= ' (' . $this->pays->getNomFrFr() . ')';
        }

        return $nom;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function __toString(): string
    {
        return $this->getDisplayName();
    }
}
