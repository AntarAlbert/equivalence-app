<?php
// src/Entity/ArreteConsiderant.php

namespace App\Entity;

use App\Repository\ArreteConsiderantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArreteConsiderantRepository::class)]
#[ORM\Table(name: 'arrete_considerant')]
class ArreteConsiderant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Arrete::class, inversedBy: 'arreteConsiderants')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Arrete $arrete = null;

    #[ORM\ManyToOne(targetEntity: Considerant::class, inversedBy: 'arreteConsiderants')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Considerant $considerant = null;

    #[ORM\Column]
    private int $ordre = 0;

    public function getId(): ?int { return $this->id; }
    public function getArrete(): ?Arrete { return $this->arrete; }
    public function setArrete(?Arrete $arrete): self { $this->arrete = $arrete; return $this; }
    public function getConsiderant(): ?Considerant { return $this->considerant; }
    public function setConsiderant(?Considerant $considerant): self { $this->considerant = $considerant; return $this; }
    public function getOrdre(): int { return $this->ordre; }
    public function setOrdre(int $ordre): self { $this->ordre = $ordre; return $this; }
}
