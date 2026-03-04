<?php
namespace App\Tests\Service;

use App\Entity\Recompense;
use App\Service\RecompenseManager;
use PHPUnit\Framework\TestCase;

class RecompenseManagerTest extends TestCase
{
    private RecompenseManager $manager;

    protected function setUp(): void
    {
        $this->manager = new RecompenseManager();
    }

    // ✅ Test 1 : Récompense valide
    public function testRecompenseValide(): void
    {
        $recompense = new Recompense();
        $recompense->setType('badge');
        $recompense->setValeur(50.0);
        $recompense->setSeuil(10);
        $recompense->setDateObtention(new \DateTime('2025-01-01'));

        $this->assertTrue($this->manager->validate($recompense));
    }

    // ❌ Test 2 : Type invalide
    public function testTypeInvalide(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("n'est pas valide");

        $recompense = new Recompense();
        $recompense->setType('trophee');

        $this->manager->validate($recompense);
    }

    // ❌ Test 3 : Valeur négative
    public function testValeurNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("La valeur de la récompense doit être positive");

        $recompense = new Recompense();
        $recompense->setType('bonus');
        $recompense->setValeur(-10.0);

        $this->manager->validate($recompense);
    }

    // ❌ Test 4 : Seuil négatif
    public function testSeuilNegatif(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Le seuil doit être un entier positif");

        $recompense = new Recompense();
        $recompense->setType('badge');
        $recompense->setSeuil(-5);

        $this->manager->validate($recompense);
    }

    // ❌ Test 5 : Date dans le futur
    public function testDateObtentionDansLeFutur(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("La date d'obtention ne peut pas être dans le futur");

        $recompense = new Recompense();
        $recompense->setType('badge');
        $recompense->setDateObtention(new \DateTime('+1 year'));

        $this->manager->validate($recompense);
    }

    // ✅ Test 6 : Type null accepté
    public function testTypeNullAccepte(): void
    {
        $recompense = new Recompense();
        $recompense->setValeur(20.0);
        $recompense->setSeuil(5);

        $this->assertTrue($this->manager->validate($recompense));
    }
}