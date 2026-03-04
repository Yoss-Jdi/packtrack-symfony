<?php
namespace App\Tests\Service;

use App\Entity\Commentaire;
use App\Service\CommentaireManager;
use PHPUnit\Framework\TestCase;

class CommentaireManagerTest extends TestCase
{
    private CommentaireManager $manager;

    protected function setUp(): void
    {
        $this->manager = new CommentaireManager();
    }

    // ✅ Test 1 : Commentaire valide
    public function testCommentaireValide(): void
    {
        $commentaire = new Commentaire();
        $commentaire->setContenu('Très bon service !');

        $this->assertTrue($this->manager->validate($commentaire));
    }

    // ❌ Test 2 : Contenu vide
    public function testContenuObligatoire(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Le commentaire est obligatoire");

        $commentaire = new Commentaire();
        $commentaire->setContenu('');

        $this->manager->validate($commentaire);
    }

    // ❌ Test 3 : Contenu trop court (1 caractère)
    public function testContenuTropCourt(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Le commentaire doit contenir au moins 2 caractères");

        $commentaire = new Commentaire();
        $commentaire->setContenu('a');

        $this->manager->validate($commentaire);
    }

    // ❌ Test 4 : Contenu trop long
    public function testContenuTropLong(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Le commentaire ne doit pas dépasser 2000 caractères");

        $commentaire = new Commentaire();
        $commentaire->setContenu(str_repeat('a', 2001));

        $this->manager->validate($commentaire);
    }

    // ✅ Test 5 : Contenu avec espaces trimé accepté
    public function testContenuAvecEspacesTrimes(): void
    {
        $commentaire = new Commentaire();
        $commentaire->setContenu('  Bon commentaire  ');

        $this->assertTrue($this->manager->validate($commentaire));
    }
}