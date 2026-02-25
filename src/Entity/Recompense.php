<?php

namespace App\Entity;

use App\Entity\Utilisateurs;  // ← CORRECTION ICI
use App\Repository\RecompenseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecompenseRepository::class)]
#[ORM\Table(name: 'recompenses')]
class Recompense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'ID_Recompense', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'type', type: 'string', length: 50, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(name: 'valeur', type: 'float', nullable: true)]
    private ?float $valeur = null;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'seuil', type: 'integer', nullable: true)]
    private ?int $seuil = null;

    #[ORM\Column(name: 'dateObtention', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateObtention = null;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]  // ← CORRECTION ICI
    #[ORM\JoinColumn(name: 'ID_Livreur', referencedColumnName: 'ID_Utilisateur', nullable: false)]
    private ?Utilisateurs $livreur = null;  // ← CORRECTION ICI

    #[ORM\ManyToOne(targetEntity: Facture::class)]
    #[ORM\JoinColumn(name: 'ID_Facture', referencedColumnName: 'ID_Facture', nullable: true, onDelete: 'SET NULL')]
    private ?Facture $facture = null;

    // ========== GETTERS & SETTERS ==========

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getValeur(): ?float
    {
        return $this->valeur;
    }

    public function setValeur(?float $valeur): static
    {
        $this->valeur = $valeur;
        return $this;
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

    public function getSeuil(): ?int
    {
        return $this->seuil;
    }

    public function setSeuil(?int $seuil): static
    {
        $this->seuil = $seuil;
        return $this;
    }

    public function getDateObtention(): ?\DateTimeInterface
    {
        return $this->dateObtention;
    }

    public function setDateObtention(?\DateTimeInterface $dateObtention): static
    {
        $this->dateObtention = $dateObtention;
        return $this;
    }

    public function getLivreur(): ?Utilisateurs  // ← CORRECTION ICI
    {
        return $this->livreur;
    }

    public function setLivreur(?Utilisateurs $livreur): static  // ← CORRECTION ICI
    {
        $this->livreur = $livreur;
        return $this;
    }

    public function getFacture(): ?Facture
    {
        return $this->facture;
    }

    public function setFacture(?Facture $facture): static
    {
        $this->facture = $facture;
        return $this;
    }
}