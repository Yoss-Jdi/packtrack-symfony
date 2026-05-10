<?php

namespace App\Entity;

use App\Repository\VehiculeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VehiculeRepository::class)]
#[ORM\Table(name: 'vehicule')]  // ⚠️ CHANGEMENT : 'vehicule' au lieu de 'vehicules'
class Vehicule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id')]  // ⚠️ CHANGEMENT : 'id' au lieu de 'ID_Vehicule'
    private ?int $id = null;

    #[ORM\Column(length: 120)]  // ⚠️ CHANGEMENT : 120 au lieu de 50, NOT NULL
    #[Assert\NotBlank(message: 'La marque est obligatoire.')]
    #[Assert\Length(max: 120)]  // ⚠️ CHANGEMENT
    private ?string $marque = null;

    #[ORM\Column(length: 120)]  // ⚠️ CHANGEMENT : 120 au lieu de 50, NOT NULL
    #[Assert\NotBlank(message: 'Le modèle est obligatoire.')]
    #[Assert\Length(max: 120)]  // ⚠️ CHANGEMENT
    private ?string $modele = null;

    #[ORM\Column(name: 'matricule', length: 120, unique: true)]  // ⚠️ CHANGEMENT : nom de colonne 'matricule' au lieu de 'immatriculation', length 120
    #[Assert\NotBlank(message: "L'immatriculation est obligatoire.")]
    #[Assert\Length(max: 120)]  // ⚠️ CHANGEMENT
    #[Assert\Regex(
        pattern: '/^[0-9]{3}tun[0-9]{4}$/i',
        message: "Le format de la plaque doit être 3 chiffres, 'tun', puis 4 chiffres (ex: 171tun7896)."
    )]
    private ?string $immatriculation = null;

    // ⚠️ SUPPRIMÉ : typeVehicule n'existe pas dans la table vehicule
    // Le champ 'type' n'existe pas dans votre table vehicule JavaFX

    // ⚠️ SUPPRIMÉ : capacite n'existe pas dans la table vehicule
    
    // ⚠️ NOUVEAU CHAMP : couleur
    #[ORM\Column(length: 80)]
    #[Assert\NotBlank(message: 'La couleur est obligatoire.')]
    #[Assert\Length(max: 80)]
    private ?string $couleur = null;

    // ⚠️ NOUVEAU CHAMP : prix_location
    #[ORM\Column(type: 'float')]
    #[Assert\NotBlank(message: 'Le prix de location est obligatoire.')]
    #[Assert\Positive(message: 'Le prix de location doit être un nombre positif.')]
    private ?float $prixLocation = null;

    // ⚠️ CHANGEMENT : 'disponible' (boolean) au lieu de 'statut' (string)
    #[ORM\Column(type: 'boolean')]
    private bool $disponible = true;

    #[ORM\ManyToOne(targetEntity: Technician::class, inversedBy: 'vehicules')]
    #[ORM\JoinColumn(name: 'technicien_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]  // ⚠️ CHANGEMENT : referencedColumnName='id'
    private ?Technician $technician = null;

    // ⚠️ SUPPRIMÉ : problemDescription n'existe pas dans la table vehicule

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(?string $marque): self
    {
        $this->marque = $marque;
        return $this;
    }

    public function getModele(): ?string
    {
        return $this->modele;
    }

    public function setModele(?string $modele): self
    {
        $this->modele = $modele;
        return $this;
    }

    public function getImmatriculation(): ?string
    {
        return $this->immatriculation;
    }

    public function setImmatriculation(string $immatriculation): self
    {
        $this->immatriculation = $immatriculation;
        return $this;
    }

    // ⚠️ NOUVEAU : getter/setter pour couleur
    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(string $couleur): self
    {
        $this->couleur = $couleur;
        return $this;
    }

    // ⚠️ NOUVEAU : getter/setter pour prixLocation
    public function getPrixLocation(): ?float
    {
        return $this->prixLocation;
    }

    public function setPrixLocation(float $prixLocation): self
    {
        $this->prixLocation = $prixLocation;
        return $this;
    }

    // ⚠️ CHANGEMENT : disponible au lieu de statut
    public function isDisponible(): bool
    {
        return $this->disponible;
    }

    public function setDisponible(bool $disponible): self
    {
        $this->disponible = $disponible;
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

    public function __toString(): string
    {
        return $this->marque . ' ' . $this->modele . ' (' . $this->immatriculation . ')';
    }
}