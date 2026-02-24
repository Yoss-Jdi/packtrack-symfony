<?php

namespace App\Entity;

use App\Repository\PublicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PublicationRepository::class)]
#[ORM\Table(name: 'publications')]
#[ORM\Index(columns: ['datePublication'], name: 'idx_publication_date')]
class Publication
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'ID_Publication', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'titre', type: 'string', length: 200)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(max: 200, maxMessage: 'Le titre ne doit pas dépasser {{ limit }} caractères.')]
    private string $titre = '';

    #[ORM\Column(name: 'contenu', type: 'text', nullable: true)]
    #[Assert\Length(max: 10000, maxMessage: 'Le contenu est trop long.')]
    private ?string $contenu = null;

    #[ORM\Column(name: 'datePublication', type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $datePublication = null;

    #[ORM\Column(name: 'statut', type: 'string', length: 50, options: ['default' => 'active'])]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['active', 'inactive'])]
    private string $statut = 'active';

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'ID_Auteur', referencedColumnName: 'ID_Utilisateur', nullable: false, onDelete: 'CASCADE')]
    private ?Utilisateurs $auteur = null;

    /**
     * @var Collection<int, Commentaire>
     */
    #[ORM\OneToMany(mappedBy: 'publication', targetEntity: Commentaire::class, orphanRemoval: true)]
    #[ORM\OrderBy(['dateCreation' => 'DESC'])]
    private Collection $commentaires;

    /**
     * @var Collection<int, PublicationReaction>
     */
    #[ORM\OneToMany(mappedBy: 'publication', targetEntity: PublicationReaction::class, orphanRemoval: true)]
    private Collection $reactions;

    public function __construct()
    {
        $this->commentaires = new ArrayCollection();
        $this->reactions = new ArrayCollection();
        $this->datePublication = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = trim($titre);
        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(?string $contenu): self
    {
        $contenu = $contenu !== null ? trim($contenu) : null;
        $this->contenu = $contenu === '' ? null : $contenu;
        return $this;
    }

    public function getDatePublication(): ?\DateTimeImmutable
    {
        return $this->datePublication;
    }

    public function setDatePublication(\DateTimeImmutable $datePublication): self
    {
        $this->datePublication = $datePublication;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->statut === 'active';
    }

    public function getAuteur(): ?Utilisateurs
    {
        return $this->auteur;
    }

    public function setAuteur(Utilisateurs $auteur): self
    {
        $this->auteur = $auteur;
        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    /**
     * @return Collection<int, PublicationReaction>
     */
    public function getReactions(): Collection
    {
        return $this->reactions;
    }
}
