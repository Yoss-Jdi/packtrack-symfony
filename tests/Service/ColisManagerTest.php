<?php
namespace App\Tests\Service;

use App\Entity\Colis;
use App\Service\ColisManager;
use PHPUnit\Framework\TestCase;

class ColisManagerTest extends TestCase
{
    private ColisManager $manager;

    protected function setUp(): void
    {
        $this->manager = new ColisManager();
    }

    // ✅ Test 1 : Colis valide
    public function testColisValide(): void
    {
        $colis = new Colis();
        $colis->setAdresseDestination('10 Rue de Paris, Tunis');
        $colis->setAdresseDepart('5 Avenue Habib Bourguiba, Sfax');
        $colis->setPoids(5.0);

        $this->assertTrue($this->manager->validate($colis));
    }

    // ❌ Test 2 : Adresse destination manquante
    public function testAdresseDestinationObligatoire(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("L'adresse de destination est obligatoire");

        $colis = new Colis();
        $colis->setAdresseDepart('5 Avenue Habib Bourguiba, Sfax');
        $colis->setPoids(5.0);

        $this->manager->validate($colis);
    }

    // ❌ Test 3 : Poids négatif
    public function testPoidsNegatif(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Le poids doit être un nombre positif");

        $colis = new Colis();
        $colis->setAdresseDestination('10 Rue de Paris, Tunis');
        $colis->setAdresseDepart('5 Avenue Habib Bourguiba, Sfax');
        $colis->setPoids(-3.0);

        $this->manager->validate($colis);
    }

    // ❌ Test 4 : Poids dépasse 1000 kg
    public function testPoidsDepasse1000(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Le poids ne peut pas dépasser 1000 kg");

        $colis = new Colis();
        $colis->setAdresseDestination('10 Rue de Paris, Tunis');
        $colis->setAdresseDepart('5 Avenue Habib Bourguiba, Sfax');
        $colis->setPoids(1500.0);

        $this->manager->validate($colis);
    }

    // ❌ Test 5 : Statut invalide
    public function testStatutInvalide(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("n'est pas valide");

        $colis = new Colis();
        $colis->setAdresseDestination('10 Rue de Paris, Tunis');
        $colis->setAdresseDepart('5 Avenue Habib Bourguiba, Sfax');
        $colis->setPoids(5.0);
        $colis->setStatut('statut_invalide');

        $this->manager->validate($colis);
    }

    // ✅ Test 6 : Calcul du montant
    public function testCalculerMontant(): void
    {
        $colis = new Colis();
        $colis->setPoids(5.0);

        // fraisDeBase(10) + poids(5) * tarifParKg(2) = 20.0
        $this->assertEquals(20.0, $this->manager->calculerMontant($colis));
    }

    // ❌ Test 7 : Calcul montant sans poids
    public function testCalculerMontantSansPoids(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Le poids est requis pour calculer le montant");

        $colis = new Colis();
        $this->manager->calculerMontant($colis);
    }
}