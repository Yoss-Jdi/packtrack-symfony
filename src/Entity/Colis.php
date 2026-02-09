<?php
namespace App\Entity;

use App\Repository\ColisRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ColisRepository::class)]
#[ORM\Table(name: 'colis')]
class Colis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'ID_Colis')]
    private ?int $id = null;

   #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        min: 5,
        max: 500,
        minMessage: "Si vous remplissez la description, elle doit contenir au moins {{ limit }} caractères",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 1000,
        maxMessage: "La liste d'articles ne peut pas dépasser {{ limit }} caractères"
    )]
    
    private ?string $articles = null;

    #[ORM\Column(name: 'adresseDestination', type: Types::TEXT)]
    #[Assert\NotBlank(message: "L'adresse de destination est obligatoire")]
    #[Assert\Length(
        min: 10,
        max: 500,
        minMessage: "L'adresse doit contenir au moins {{ limit }} caractères",
        maxMessage: "L'adresse ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $adresseDestination = null;

    #[ORM\Column(name: 'dateCreation', type: Types::DATETIME_MUTABLE, nullable: true, columnDefinition: 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP')]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(name: 'dateExpedition', type: Types::DATETIME_MUTABLE, nullable: true, columnDefinition: 'TIMESTAMP NULL DEFAULT NULL')]
    private ?\DateTimeInterface $dateExpedition = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotBlank(message: "Le poids est obligatoire (nécessaire pour calculer le montant)")]
    #[Assert\Positive(message: "Le poids doit être un nombre positif")]
    #[Assert\LessThan(
        value: 1000,
        message: "Le poids ne peut pas dépasser {{ compared_value }} kg"
    )]
    private ?float $poids = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(
        max: 100,
        maxMessage: "Les dimensions ne peuvent pas dépasser {{ limit }} caractères"
    )]
    
    private ?string $dimensions = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Choice(
        choices: ['en_attente', 'en_cours', 'livre'],
        message: "Le statut '{{ value }}' n'est pas valide. Valeurs autorisées : {{ choices }}"
    )]
    private ?string $statut = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'ID_Expediteur', referencedColumnName: 'ID_Utilisateur', nullable: false)]
    #[Assert\NotNull(message: "L'expéditeur est obligatoire")]
    private ?Utilisateur $expediteur = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'ID_Destinataire', referencedColumnName: 'ID_Utilisateur', nullable: false)]
    #[Assert\NotNull(message: "Le destinataire est obligatoire")]
    private ?Utilisateur $destinataire = null;

    #[ORM\OneToMany(mappedBy: 'colis', targetEntity: Livraison::class)]
    private Collection $livraisons;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->statut = 'en_attente';
        $this->livraisons = new ArrayCollection(); 
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getArticles(): ?string
    {
        return $this->articles;
    }

    public function setArticles(?string $articles): static
    {
        $this->articles = $articles;
        return $this;
    }

    public function getAdresseDestination(): ?string
    {
        return $this->adresseDestination;
    }

    public function setAdresseDestination(string $adresseDestination): static
    {
        $this->adresseDestination = $adresseDestination;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getDateExpedition(): ?\DateTimeInterface
    {
        return $this->dateExpedition;
    }

    public function setDateExpedition(?\DateTimeInterface $dateExpedition): static
    {
        $this->dateExpedition = $dateExpedition;
        return $this;
    }

    public function getPoids(): ?float
    {
        return $this->poids;
    }

    public function setPoids(?float $poids): static
    {
        $this->poids = $poids;
        return $this;
    }

    public function getDimensions(): ?string
    {
        return $this->dimensions;
    }

    public function setDimensions(?string $dimensions): static
    {
        $this->dimensions = $dimensions;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getExpediteur(): ?Utilisateur
    {
        return $this->expediteur;
    }

    public function setExpediteur(?Utilisateur $expediteur): static
    {
        $this->expediteur = $expediteur;
        return $this;
    }

    public function getDestinataire(): ?Utilisateur
    {
        return $this->destinataire;
    }

    public function setDestinataire(?Utilisateur $destinataire): static
    {
        $this->destinataire = $destinataire;
        return $this;
    }
    public function getLivraisons(): Collection
    {
        return $this->livraisons;
    }

    public function estDisponible(): bool
    {
        return $this->livraisons->isEmpty() && $this->statut === 'en_attente';
    }
    public function calculerMontant(): float
    {
        $poids = $this->poids ?? 0;
        $fraisDeBase = 10.0;
        $tarifParKg = 2.0;
        
        return $fraisDeBase + ($poids * $tarifParKg);
    }
}