<?php

namespace App\Entity;

use App\Repository\PasswordResetTokenRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;
use SensitiveParameter;
use Symfony\Component\Uid\UuidV7;

#[ORM\Entity(repositoryClass: PasswordResetTokenRepository::class)]
#[ORM\Table(name: 'password_reset_tokens')]
class PasswordResetToken
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private UuidV7 $id;

    // FIX: non-nullable string (was ?string)
    #[ORM\Column(type: 'string', length: 255)]
    private string $email = '';

    // FIX: non-nullable string (was ?string)
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Ignore]
    private string $token = '';

    // FIX: non-nullable \DateTimeInterface (was ?\DateTimeInterface)
    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $expiresAt;

    // FIX: non-nullable \DateTimeInterface (was ?\DateTimeInterface)
    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'boolean')]
    private bool $used = false;

    public function __construct()
    {
        $this->id = new UuidV7();
        $this->createdAt = new \DateTime();
    }

    public function getId(): UuidV7
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(#[SensitiveParameter] string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getExpiresAt(): \DateTimeInterface
    {
        return $this->expiresAt;
    }

    // FIX: kept setter for expiresAt as it must be set explicitly at creation (not auto-managed)
    public function setExpiresAt(\DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    // FIX: removed public setCreatedAt() - set automatically in constructor

    public function isUsed(): bool
    {
        return $this->used;
    }

    public function setUsed(bool $used): self
    {
        $this->used = $used;
        return $this;
    }

    public function isExpired(): bool
    {
        return new \DateTime() > $this->expiresAt;
    }

    public function isValid(): bool
    {
        return !$this->used && !$this->isExpired();
    }
}