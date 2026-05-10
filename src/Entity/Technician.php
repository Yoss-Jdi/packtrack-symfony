<?php

namespace App\Entity;

use App\Repository\TechnicianRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TechnicianRepository::class)]
#[ORM\Table(name: 'technicien')]  // ✅ Déjà correct
class Technician
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id')]  // ⚠️ CHANGEMENT ICI : 'id' au lieu de 'ID_Technicien'
    private ?int $id = null;

    #[ORM\Column(length: 120)]  // ⚠️ CHANGEMENT : 120 au lieu de 100
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]  // ⚠️ CHANGEMENT
    private ?string $nom = null;

    #[ORM\Column(length: 120)]  // ⚠️ CHANGEMENT : 120 au lieu de 100
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]  // ⚠️ CHANGEMENT
    private ?string $prenom = null;

    #[ORM\Column(length: 150)]  // ⚠️ CHANGEMENT : 150 au lieu de 100, NOT NULL
    #[Assert\NotBlank]  // ⚠️ CHANGEMENT : obligatoire maintenant
    #[Assert\Length(max: 150)]  // ⚠️ CHANGEMENT
    private ?string $specialite = null;

    #[ORM\Column(length: 50)]  // ⚠️ CHANGEMENT : 50 au lieu de 20, NOT NULL
    #[Assert\NotBlank]  // ⚠️ CHANGEMENT : obligatoire maintenant
    #[Assert\Length(max: 50)]  // ⚠️ CHANGEMENT
    private ?string $telephone = null;

    #[ORM\Column(length: 190, unique: true)]  // ✅ Correct
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 190)]  // ⚠️ CHANGEMENT : 190 au lieu de 180
    private ?string $email = null;

    // ⚠️ SUPPRIMER le champ statut - il n'existe pas dans la table technicien
    // #[ORM\Column(length: 50)]
    // private string $statut = 'disponible';

    /** @var Collection<int, Vehicule> */
    #[ORM\OneToMany(mappedBy: 'technician', targetEntity: Vehicule::class)]
    private Collection $vehicules;

    public function __construct()
    {
        $this->vehicules = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(string $specialite): self  // ⚠️ Plus nullable
    {
        $this->specialite = $specialite;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self  // ⚠️ Plus nullable
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    // ⚠️ SUPPRIMER les méthodes getStatut() et setStatut()
    // public function getStatut(): ?string { ... }
    // public function setStatut(string $statut): self { ... }

    /**
     * @return Collection<int, Vehicule>
     */
    public function getVehicules(): Collection
    {
        return $this->vehicules;
    }

    public function addVehicule(Vehicule $vehicule): self
    {
        if (!$this->vehicules->contains($vehicule)) {
            $this->vehicules[] = $vehicule;
            $vehicule->setTechnician($this);
        }

        return $this;
    }

    public function removeVehicule(Vehicule $vehicule): self
    {
        if ($this->vehicules->removeElement($vehicule)) {
            if ($vehicule->getTechnician() === $this) {
                $vehicule->setTechnician(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }
}