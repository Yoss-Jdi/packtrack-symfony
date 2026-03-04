<?php
namespace App\Tests\Service;

use App\Entity\Publication;
use App\Service\PublicationManager;
use PHPUnit\Framework\TestCase;

class PublicationManagerTest extends TestCase
{
    private PublicationManager $manager;

    protected function setUp(): void
    {
        $this->manager = new PublicationManager();
    }

    // ✅ Test 1 : Publication valide
    public function testPublicationValide(): void
    {
        $pub = new Publication();
        $pub->setTitre('Mon titre valide');
        $pub->setContenu('Contenu de la publication');
        $pub->setStatut('active');

        $this->assertTrue($this->manager->validate($pub));
    }

    // ❌ Test 2 : Titre vide
    public function testTitreObligatoire(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Le titre est obligatoire");

        $pub = new Publication();
        $pub->setTitre('');

        $this->manager->validate($pub);
    }

    // ❌ Test 3 : Titre trop long
    public function testTitreTropLong(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Le titre ne doit pas dépasser 200 caractères");

        $pub = new Publication();
        $pub->setTitre(str_repeat('a', 201));

        $this->manager->validate($pub);
    }

    // ❌ Test 4 : Contenu trop long
    public function testContenuTropLong(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Le contenu est trop long");

        $pub = new Publication();
        $pub->setTitre('Titre valide');
        $pub->setContenu(str_repeat('a', 10001));

        $this->manager->validate($pub);
    }

    // ❌ Test 5 : Statut invalide
    public function testStatutInvalide(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("n'est pas valide");

        $pub = new Publication();
        $pub->setTitre('Titre valide');
        $pub->setStatut('brouillon');

        $this->manager->validate($pub);
    }

    // ✅ Test 6 : isActive retourne true
    public function testIsActiveTrue(): void
    {
        $pub = new Publication();
        $pub->setStatut('active');

        $this->assertTrue($this->manager->isActive($pub));
    }

    // ✅ Test 7 : isActive retourne false
    public function testIsActiveFalse(): void
    {
        $pub = new Publication();
        $pub->setStatut('inactive');

        $this->assertFalse($this->manager->isActive($pub));
    }
}