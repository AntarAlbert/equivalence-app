<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Index(columns: ['numero_cni'])]
#[ORM\Index(columns: ['nom_normalise', 'prenom_normalise'])]
class DiplomeObtenu
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Diplome::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Diplome $diplome = null;

    #[ORM\Column(length: 100)]
    private string $nom;

    #[ORM\Column(length: 100)]
    private string $prenom;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $nomNormalise = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $prenomNormalise = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $numeroCni = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $anneeObtention = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $numeroDiplome = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mention = null;

    #[ORM\Column(nullable: true)]
    private ?float $moyenne = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $soumisPar = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // Normalisation auto
    public function setNom(string $nom): self
    {
        $this->nom = trim($nom);
        $this->nomNormalise = mb_strtoupper(trim($nom));
        return $this;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = trim($prenom);
        $this->prenomNormalise = mb_strtoupper(trim($prenom));
        return $this;
    }

    // Getters/Setters...
}
