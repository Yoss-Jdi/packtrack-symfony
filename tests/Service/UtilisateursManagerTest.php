<?php

namespace App\Tests\Service;

use App\Entity\Role;
use App\Entity\Utilisateurs;
use App\Service\UtilisateursManager;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class UtilisateursManagerTest extends TestCase
{
    private UtilisateursManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new UtilisateursManager();
    }

    public function testUtilisateurValide(): void
    {
        $user = new Utilisateurs();
        $user->setEmail('yassine@bargaoui.tn');
        $user->setMotDePasse('Yassine123');

        $this->assertTrue($this->manager->validate($user));
    }

    public function testEmailVide(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $user = new Utilisateurs();
        $user->setEmail('');
        $user->setMotDePasse('Yassine123');

        $this->manager->validate($user);
    }

    public function testMotDePasseTropCourt(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $user = new Utilisateurs();
        $user->setEmail('yassine@bargaoui.tn');
        $user->setMotDePasse('123');

        $this->manager->validate($user);
    }

    public function testGetRolesAvecRoleAdmin(): void
    {
        $user = new Utilisateurs();
        $user->setRole(Role::ADMIN);

        $this->assertEquals(['ROLE_ADMIN'], $user->getRoles());
    }

    public function testGetRolesAvecRoleClient(): void
    {
        $user = new Utilisateurs();
        $user->setRole(Role::CLIENT);

        $this->assertEquals(['ROLE_CLIENT'], $user->getRoles());
    }

    public function testGetUserIdentifierRetourneEmail(): void
    {
        $user = new Utilisateurs();
        $user->setEmail('test@gmail.com');

        $this->assertEquals('test@gmail.com', $user->getUserIdentifier());
    }

    public function testCreatedAtEstDefiniAutomatiquement(): void
    {
        $user = new Utilisateurs();

        $this->assertInstanceOf(DateTimeImmutable::class, $user->getCreatedAt());
    }

    public function testTelephoneEstNullable(): void
    {
        $user = new Utilisateurs();
        $user->setTelephone(null);

        $this->assertNull($user->getTelephone());
    }
}
