<?php

namespace App\Entity;

use App\Repository\VehiculeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VehiculeRepository::class)]
#[ORM\Table(name: 'vehicules')]
class Vehicule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'ID_Vehicule')]
    private ?int $id = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\NotBlank(message: 'La marque est obligatoire.')]
    #[Assert\Length(max: 50)]
    private ?string $marque = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\NotBlank(message: 'Le modèle est obligatoire.')]
    #[Assert\Length(max: 50)]
    private ?string $modele = null;

    #[ORM\Column(length: 20, unique: true)]
    #[Assert\NotBlank(message: "L'immatriculation est obligatoire.")]
    #[Assert\Length(max: 20)]
    #[Assert\Regex(
        pattern: '/^[0-9]{3}tun[0-9]{4}$/i',
        message: "Le format de la plaque doit être 3 chiffres, 'tun', puis 4 chiffres (ex: 171tun7896)."
    )]
    private ?string $immatriculation = null;

    #[ORM\Column(name: 'type', length: 50, nullable: true)]
    #[Assert\NotBlank(message: 'Le type de véhicule est obligatoire.')]
    #[Assert\Length(max: 50)]
    private ?string $typeVehicule = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotBlank(message: 'La capacité est obligatoire.')]
    #[Assert\Positive(message: 'La capacité doit être un nombre positif.')]
    private ?float $capacite = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['disponible', 'en_maintenance', 'hors_service'])]
    private ?string $statut = 'disponible';

    #[ORM\ManyToOne(targetEntity: Technician::class, inversedBy: 'vehicules')]
    #[ORM\JoinColumn(name: 'ID_Technicien', referencedColumnName: 'ID_Technicien', nullable: true, onDelete: 'SET NULL')]
    private ?Technician $technician = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $problemDescription = null;

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

    public function getTypeVehicule(): ?string
    {
        return $this->typeVehicule;
    }

    public function setTypeVehicule(?string $typeVehicule): self
    {
        $this->typeVehicule = $typeVehicule;
        return $this;
    }

    public function getCapacite(): ?float
    {
        return $this->capacite;
    }

    public function setCapacite(?float $capacite): self
    {
        $this->capacite = $capacite;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
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

    public function getProblemDescription(): ?string
    {
        return $this->problemDescription;
    }

    public function setProblemDescription(?string $problemDescription): self
    {
        $this->problemDescription = $problemDescription;
        return $this;
    }
}
