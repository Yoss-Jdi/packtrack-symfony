<?php
namespace App\Tests\Service;

use App\Entity\Facture;
use App\Service\FactureManager;
use PHPUnit\Framework\TestCase;

class FactureManagerTest extends TestCase
{
    private FactureManager $manager;

    protected function setUp(): void
    {
        $this->manager = new FactureManager();
    }

    // ✅ Test 1 : Facture valide
    public function testFactureValide(): void
    {
        $facture = new Facture();
        $facture->setNumero('FAC-001');
        $facture->setMontantHT(100.0);
        $facture->setMontantTTC(120.0);

        $this->assertTrue($this->manager->validate($facture));
    }

    // ❌ Test 2 : Numéro manquant
    public function testNumeroObligatoire(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Le numéro de facture est obligatoire");

        $facture = new Facture();
        $facture->setMontantHT(100.0);

        $this->manager->validate($facture);
    }

    // ❌ Test 3 : Format numéro invalide
    public function testNumeroFormatInvalide(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Le numéro de facture doit suivre le format FAC-XXX");

        $facture = new Facture();
        $facture->setNumero('FACTURE-001');
        $facture->setMontantHT(100.0);

        $this->manager->validate($facture);
    }

    // ❌ Test 4 : Montant HT négatif
    public function testMontantHTNegatif(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Le montant HT doit être positif");

        $facture = new Facture();
        $facture->setNumero('FAC-002');
        $facture->setMontantHT(-50.0);

        $this->manager->validate($facture);
    }

    // ❌ Test 5 : MontantTTC inférieur au montantHT
    public function testMontantTTCInferieurHT(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Le montant TTC ne peut pas être inférieur au montant HT");

        $facture = new Facture();
        $facture->setNumero('FAC-003');
        $facture->setMontantHT(100.0);
        $facture->setMontantTTC(80.0);

        $this->manager->validate($facture);
    }

    // ✅ Test 6 : Calcul TTC avec TVA 20%
    public function testCalculerMontantTTC(): void
    {
        $facture = new Facture();
        $facture->setMontantHT(100.0);
        $facture->setTva(20.0);

        // 100 * (1 + 20/100) = 120.0
        $this->assertEquals(120.0, $this->manager->calculerMontantTTC($facture));
    }

    // ✅ Test 7 : Calcul TTC sans TVA
    public function testCalculerMontantTTCSansTva(): void
    {
        $facture = new Facture();
        $facture->setMontantHT(100.0);
        $facture->setTva(0.0);

        $this->assertEquals(100.0, $this->manager->calculerMontantTTC($facture));
    }

    // ❌ Test 8 : Calcul TTC sans montant HT
    public function testCalculerMontantTTCSansMontantHT(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Le montant HT est requis pour calculer le TTC");

        $facture = new Facture();
        $this->manager->calculerMontantTTC($facture);
    }
}