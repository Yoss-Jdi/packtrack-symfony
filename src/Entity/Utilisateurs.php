<?php

namespace App\Entity;

use App\Repository\UtilisateursRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UtilisateursRepository::class)]
class Utilisateurs implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'ID_Utilisateur')]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $Email = null;

    #[ORM\Column(length: 255)]
    private ?string $MotDePasse = null;

    #[ORM\Column(length: 100)]
    private ?string $Nom = null;

    #[ORM\Column(length: 100)]
    private ?string $Prenom = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $Telephone = null;

    #[ORM\Column(type: Types::STRING, enumType: Role::class)]
    private ?Role $role = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    // ====== NOUVEAU CHAMP ======
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    // ====== CONSTRUCTEUR ======
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable(); // date de création automatique
    }

    // ====== GETTERS ET SETTERS ======
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->Email;
    }

    public function setEmail(string $Email): static
    {
        $this->Email = $Email;
        return $this;
    }

    public function getMotDePasse(): ?string
    {
        return $this->MotDePasse;
    }

    public function setMotDePasse(string $MotDePasse): static
    {
        $this->MotDePasse = $MotDePasse;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->MotDePasse;
    }

    public function getNom(): ?string
    {
        return $this->Nom;
    }

    public function setNom(string $Nom): static
    {
        $this->Nom = $Nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->Prenom;
    }

    public function setPrenom(string $Prenom): static
    {
        $this->Prenom = $Prenom;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->Telephone;
    }

    public function setTelephone(?string $Telephone): static
    {
        $this->Telephone = $Telephone;
        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(Role $role): static
    {
        $this->role = $role;
        return $this;
    }

    // ====== GETTER/SETTER POUR CREATEDAT ======
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    // ====== MÉTHODES UserInterface ======
    public function getRoles(): array
    {
        return ['ROLE_' . $this->role->value];
    }

    public function getPhoto(): ?string
{
    return $this->photo;
}

public function setPhoto(?string $photo): static
{
    $this->photo = $photo;
    return $this;
}

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->Email;
    }
}
