<?php

namespace App\Entity;

use App\Repository\LivraisonRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LivraisonRepository::class)]
#[ORM\Table(name: 'livraisons')]
class Livraison
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'ID_Livraison')]
    private ?int $id = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Choice(
        choices: ['en_cours', 'termine'],
        message: "Le statut '{{ value }}' n'est pas valide. Valeurs autorisées : {{ choices }}"
    )]
    private ?string $statut = 'en_cours';

    #[ORM\Column(name: 'dateDebut', type: Types::DATETIME_MUTABLE, nullable: true, columnDefinition: 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP')]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(name: 'dateFin', type: Types::DATETIME_MUTABLE, nullable: true, columnDefinition: 'TIMESTAMP NULL DEFAULT NULL')]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(name: 'distanceKm', nullable: true)]
    #[Assert\Positive(message: "La distance doit être un nombre positif")]
    #[Assert\LessThan(
        value: 10000,
        message: "La distance ne peut pas dépasser {{ compared_value }} km"
    )]
    private ?float $distanceKm = null;

    #[ORM\Column(nullable: true)]
    //#[Assert\PositiveOrZero(message: "Le paiement ne peut pas être négatif")]
    private ?float $payment = null;

    #[ORM\Column(nullable: true)]
    //#[Assert\PositiveOrZero(message: "Le total ne peut pas être négatif")]
    private ?float $total = null;

    #[ORM\Column(name: 'methodePaiement', length: 50, nullable: true)]
    #[Assert\Choice(
        choices: ['carte', 'especes', 'virement', 'cheque'],
        message: "La méthode de paiement '{{ value }}' n'est pas valide"
    )]
    private ?string $methodePaiement = null;

    #[ORM\ManyToOne(targetEntity: Colis::class)]
    #[ORM\JoinColumn(name: 'ID_Colis', referencedColumnName: 'ID_Colis', nullable: false)]
    #[Assert\NotNull(message: "Le colis est obligatoire")]
    private ?Colis $colis = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'ID_Livreur', referencedColumnName: 'ID_Utilisateur', nullable: false)]
    #[Assert\NotNull(message: "Le livreur est obligatoire")]
    private ?Utilisateur $livreur = null;

    public function __construct()
    {
        $this->dateDebut = new \DateTime();
        $this->statut = 'en_cours';
    }
    

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getDistanceKm(): ?float
    {
        return $this->distanceKm;
    }

    public function setDistanceKm(?float $distanceKm): static
    {
        $this->distanceKm = $distanceKm;
        return $this;
    }

    public function getPayment(): ?float
    {
        return $this->payment;
    }

    public function setPayment(?float $payment): static
    {
        $this->payment = $payment;
        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(?float $total): static
    {
        $this->total = $total;
        return $this;
    }

    public function getMethodePaiement(): ?string
    {
        return $this->methodePaiement;
    }

    public function setMethodePaiement(?string $methodePaiement): static
    {
        $this->methodePaiement = $methodePaiement;
        return $this;
    }

    public function getColis(): ?Colis
    {
        return $this->colis;
    }

    public function setColis(?Colis $colis): static
    {
        $this->colis = $colis;
        return $this;
    }

    public function getLivreur(): ?Utilisateur
    {
        return $this->livreur;
    }

    public function setLivreur(?Utilisateur $livreur): static
    {
        $this->livreur = $livreur;
        return $this;
    }
}