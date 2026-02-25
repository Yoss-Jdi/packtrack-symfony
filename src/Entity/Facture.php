<?php

namespace App\Entity;

use App\Repository\FactureRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: FactureRepository::class)]
#[ORM\Table(name: "factures")]
#[UniqueEntity(
    fields: ['numero'],
    message: 'Ce numéro de facture existe déjà. Choisissez-en un autre.'
)]
class Facture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "ID_Facture", type: "integer")]
    private ?int $ID_Facture = null;

    #[ORM\Column(name: "numero", length: 50, unique: true)]
    #[Assert\NotBlank(message: "Le numéro de facture est obligatoire.")]
    #[Assert\Length(
        max: 50,
        maxMessage: "Le numéro de facture ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: "/^FAC-\d{3}$/",
        message: "Le numéro de facture doit suivre le format FAC-XXX (ex: FAC-006)."
    )]
    private ?string $numero = null;

    #[ORM\Column(name: "dateEmission", type: "datetime", nullable: true)]
    #[Assert\Type("\DateTimeInterface")]
    private ?\DateTimeInterface $dateEmission = null;

    #[ORM\Column(name: "montantHT", type: "float")]
    #[Assert\NotNull(message: "Le montant HT est obligatoire.")]
    #[Assert\Positive(message: "Le montant HT doit être positif.")]
    private ?float $montantHT = null;

    #[ORM\Column(name: "montantTTC", type: "float")]
    private ?float $montantTTC = null;

    #[ORM\Column(name: "tva", type: "float", nullable: true)]
    private ?float $tva = 0.0;

    #[ORM\Column(name: "statut", type: "string", length: 50, nullable: true)]
    private ?string $statut = 'emise';


    #[ORM\Column(name: "pdfUrl", type: "string", length: 500, nullable: true)]
private ?string $pdfUrl = null;

public function getPdfUrl(): ?string
{
    return $this->pdfUrl;
}

public function setPdfUrl(?string $pdfUrl): self
{
    $this->pdfUrl = $pdfUrl;
    return $this;
}


#[ORM\ManyToOne(targetEntity: Livraison::class)]
#[ORM\JoinColumn(name: "ID_Livraison", referencedColumnName: "ID_Livraison", nullable: false)]
#[Assert\NotNull(message: "La livraison est obligatoire.")]
private ?Livraison $livraison = null;









    // -------- GETTERS / SETTERS --------

    public function getId(): ?int
    {
        return $this->ID_Facture;
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

    public function setDateEmission(?\DateTimeInterface $dateEmission): self
    {
        $this->dateEmission = $dateEmission;
        return $this;
    }

    public function getMontantHT(): ?float
    {
        return $this->montantHT;
    }

    public function setMontantHT(float $montantHT): self
    {
        $this->montantHT = $montantHT;
        return $this;
    }

    public function getMontantTTC(): ?float
    {
        return $this->montantTTC;
    }

    public function setMontantTTC(float $montantTTC): self
    {
        $this->montantTTC = $montantTTC;
        return $this;
    }

    public function getTva(): ?float
    {
        return $this->tva;
    }

    public function setTva(?float $tva): self
    {
        $this->tva = $tva;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }





public function getLivraison(): ?Livraison
{
    return $this->livraison;
}

public function setLivraison(?Livraison $livraison): self
{
    $this->livraison = $livraison;
    return $this;
}



}
