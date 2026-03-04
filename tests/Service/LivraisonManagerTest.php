<?php
namespace App\Tests\Service;

use App\Entity\Livraison;
use App\Service\LivraisonManager;
use PHPUnit\Framework\TestCase;

class LivraisonManagerTest extends TestCase
{
    private LivraisonManager $manager;

    protected function setUp(): void
    {
        $this->manager = new LivraisonManager();
    }

    // ✅ Test 1 : Livraison valide
    public function testLivraisonValide(): void
    {
        $livraison = new Livraison();
        $livraison->setDateDebut(new \DateTime('2025-01-01 08:00:00'));
        $livraison->setDateFin(new \DateTime('2025-01-01 12:00:00'));
        $livraison->setDistanceKm(50.0);
        $livraison->setMethodePaiement('carte');

        $this->assertTrue($this->manager->validate($livraison));
    }

    // ❌ Test 2 : Date de fin avant date de début
    public function testDateFinAvantDateDebut(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("La date de fin doit être postérieure à la date de début");

        $livraison = new Livraison();
        $livraison->setDateDebut(new \DateTime('2025-01-01 12:00:00'));
        $livraison->setDateFin(new \DateTime('2025-01-01 08:00:00'));

        $this->manager->validate($livraison);
    }

    // ❌ Test 3 : Distance négative
    public function testDistanceNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("La distance doit être un nombre positif");

        $livraison = new Livraison();
        $livraison->setDistanceKm(-10.0);

        $this->manager->validate($livraison);
    }

    // ❌ Test 4 : Distance dépasse 10000 km
    public function testDistanceDepasse10000(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("La distance ne peut pas dépasser 10000 km");

        $livraison = new Livraison();
        $livraison->setDistanceKm(15000.0);

        $this->manager->validate($livraison);
    }

    // ❌ Test 5 : Méthode de paiement invalide
    public function testMethodePaiementInvalide(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("n'est pas valide");

        $livraison = new Livraison();
        $livraison->setMethodePaiement('bitcoin');

        $this->manager->validate($livraison);
    }

    // ✅ Test 6 : Format durée en minutes seulement
    public function testDureeFormateeMinutes(): void
    {
        $livraison = new Livraison();
        $livraison->setDureeEstimeeMinutes(45.0);

        $this->assertEquals('45 min', $livraison->getDureeFormatee());
    }

    // ✅ Test 7 : Format durée en heures et minutes
    public function testDureeFormateeHeuresEtMinutes(): void
    {
        $livraison = new Livraison();
        $livraison->setDureeEstimeeMinutes(90.0);

        $this->assertEquals('1h 30min', $livraison->getDureeFormatee());
    }
}