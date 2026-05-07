<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Document
{
    public const TYPE_DIPLOME = 'DIPLOME';
    public const TYPE_RELEVE = 'RELEVE';
    public const TYPE_CIN = 'CIN';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $filename;

    #[ORM\Column(length: 255)]
    private string $originalName;

    #[ORM\Column(length: 255)]
    private string $path;

    #[ORM\Column(length: 50)]
    private string $type;  // DIPLOME, RELEVE, CIN

    #[ORM\Column(nullable: true)]
    private ?int $size = null; // taille en octets

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $mimeType = null;

    #[ORM\Column]
    private \DateTimeImmutable $uploadedAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Equivalence $equivalence = null;

    public function __construct()
    {
        $this->uploadedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // --- Getters / Setters ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;
        return $this;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function setOriginalName(string $originalName): self
    {
        $this->originalName = $originalName;
        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
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

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;
        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): self
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    public function getUploadedAt(): \DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(\DateTimeImmutable $uploadedAt): self
    {
        $this->uploadedAt = $uploadedAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getEquivalence(): ?Equivalence
    {
        return $this->equivalence;
    }

    public function setEquivalence(?Equivalence $equivalence): self
    {
        $this->equivalence = $equivalence;
        return $this;
    }

    // --- Helpers pratiques ---

    public function isPdf(): bool
    {
        return $this->mimeType === 'application/pdf';
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mimeType, 'image');
    }

    public function getReadableSize(): string
    {
        $size = $this->size ?? 0;
        if ($size >= 1048576) {
            return round($size / 1048576, 2) . ' Mo';
        }
        if ($size >= 1024) {
            return round($size / 1024, 2) . ' Ko';
        }
        return $size . ' o';
    }
}
