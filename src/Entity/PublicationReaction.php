<?php

namespace App\Entity;

use App\Repository\PublicationReactionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PublicationReactionRepository::class)]
#[ORM\Table(name: 'publication_reactions')]
#[ORM\UniqueConstraint(name: 'uniq_publication_auteur', columns: ['ID_Publication', 'ID_Auteur'])]
class PublicationReaction
{
    public const LIKE = 1;
    public const DISLIKE = -1;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'ID_Reaction', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'reaction', type: 'smallint')]
    #[Assert\Choice(choices: [self::LIKE, self::DISLIKE])]
    private int $reaction = self::LIKE;

    #[ORM\Column(name: 'dateCreation', type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Publication::class, inversedBy: 'reactions')]
    #[ORM\JoinColumn(name: 'ID_Publication', referencedColumnName: 'ID_Publication', nullable: false, onDelete: 'CASCADE')]
    private ?Publication $publication = null;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'ID_Auteur', referencedColumnName: 'ID_Utilisateur', nullable: false, onDelete: 'CASCADE')]
    private ?Utilisateurs $auteur = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReaction(): int
    {
        return $this->reaction;
    }

    public function setReaction(int $reaction): self
    {
        $this->reaction = $reaction;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function getPublication(): ?Publication
    {
        return $this->publication;
    }

    public function setPublication(Publication $publication): self
    {
        $this->publication = $publication;
        return $this;
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
}
