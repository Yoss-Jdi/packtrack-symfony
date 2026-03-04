<?php

namespace App\Entity;

use App\Repository\FactureMaintenanceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FactureMaintenanceRepository::class)]
#[ORM\Table(name: 'factures_maintenance')]
class FactureMaintenance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'ID_Facture_Maintenance')]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank(message: 'Le numéro de facture est obligatoire.')]
    private ?string $numero = null;

    #[ORM\Column(name: 'date_emission', type: 'datetime')]
    private ?\DateTimeInterface $dateEmission = null;

    #[ORM\Column(name: 'montant_ht', type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Le montant HT est obligatoire.')]
    #[Assert\Positive(message: 'Le montant HT doit être positif.')]
    private ?string $montantHT = null;

    #[ORM\Column(name: 'montant_ttc', type: 'decimal', precision: 10, scale: 2)]
    private ?string $montantTTC = null;

    #[ORM\Column(name: 'taux_tva', type: 'decimal', precision: 5, scale: 2)]
    #[Assert\NotBlank(message: 'Le taux de TVA est obligatoire.')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux de TVA doit être entre {{ min }}% et {{ max }}%.')]
    private ?string $tauxTVA = null;

    #[ORM\Column(name: 'description_travaux', type: 'text')]
    #[Assert\NotBlank(message: 'La description des travaux est obligatoire.')]
    private ?string $descriptionTravaux = null;

    #[ORM\ManyToOne(targetEntity: Vehicule::class)]
    #[ORM\JoinColumn(name: 'vehicule_id', referencedColumnName: 'ID_Vehicule', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'Le véhicule est obligatoire.')]
    private ?Vehicule $vehicule = null;

    #[ORM\ManyToOne(targetEntity: Technician::class)]
    #[ORM\JoinColumn(name: 'technicien_id', referencedColumnName: 'ID_Technicien', nullable: true, onDelete: 'SET NULL')]
    private ?Technician $technician = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fournisseur = null;

    #[ORM\Column(name: 'piece_changees', type: 'text', nullable: true)]
    private ?string $pieceChangees = null;

    public function __construct()
    {
        $this->dateEmission = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): self
    {
        $this->numero = $numero;
        return $this;
    }

    public function getDateEmission(): ?\DateTimeInterface
    {
        return $this->dateEmission;
    }

    public function setDateEmission(\DateTimeInterface $dateEmission): self
    {
        $this->dateEmission = $dateEmission;
        return $this;
    }

    public function getMontantHT(): ?string
    {
        return $this->montantHT;
    }

    public function setMontantHT(string $montantHT): self
    {
        $this->montantHT = $montantHT;
        return $this;
    }

    public function getMontantTTC(): ?string
    {
        return $this->montantTTC;
    }

    public function setMontantTTC(string $montantTTC): self
    {
        $this->montantTTC = $montantTTC;
        return $this;
    }

    public function getTauxTVA(): ?string
    {
        return $this->tauxTVA;
    }

    public function setTauxTVA(string $tauxTVA): self
    {
        $this->tauxTVA = $tauxTVA;
        return $this;
    }

    public function getDescriptionTravaux(): ?string
    {
        return $this->descriptionTravaux;
    }

    public function setDescriptionTravaux(string $descriptionTravaux): self
    {
        $this->descriptionTravaux = $descriptionTravaux;
        return $this;
    }

    public function getVehicule(): ?Vehicule
    {
        return $this->vehicule;
    }

    public function setVehicule(?Vehicule $vehicule): self
    {
        $this->vehicule = $vehicule;
        return $this;
    }

    public function getTechnician(): ?Technician
    {
        return $this->technician;
    }

    public function setTechnician(?Technician $technician): self
    {
        $this->technician = $technician;
        return $this;
    }

    public function getFournisseur(): ?string
    {
        return $this->fournisseur;
    }

    public function setFournisseur(?string $fournisseur): self
    {
        $this->fournisseur = $fournisseur;
        return $this;
    }

    public function getPieceChangees(): ?string
    {
        return $this->pieceChangees;
    }

    public function setPieceChangees(?string $pieceChangees): self
    {
        $this->pieceChangees = $pieceChangees;
        return $this;
    }
}
