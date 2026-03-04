<?php
namespace App\Service;

use App\Entity\Facture;

class FactureManager
{
    private const NUMERO_PATTERN = '/^FAC-\d{3}$/';

    public function validate(Facture $facture): bool
    {
        // Règle 1 : Numéro obligatoire et format FAC-XXX
        if (empty($facture->getNumero())) {
            throw new \InvalidArgumentException("Le numéro de facture est obligatoire");
        }
        if (!preg_match(self::NUMERO_PATTERN, $facture->getNumero())) {
            throw new \InvalidArgumentException("Le numéro de facture doit suivre le format FAC-XXX (ex: FAC-006)");
        }

        // Règle 2 : Montant HT obligatoire et positif
        if ($facture->getMontantHT() === null) {
            throw new \InvalidArgumentException("Le montant HT est obligatoire");
        }
        if ($facture->getMontantHT() <= 0) {
            throw new \InvalidArgumentException("Le montant HT doit être positif");
        }

        // Règle 3 : MontantTTC doit être >= MontantHT
        if ($facture->getMontantTTC() !== null && $facture->getMontantTTC() < $facture->getMontantHT()) {
            throw new \InvalidArgumentException("Le montant TTC ne peut pas être inférieur au montant HT");
        }

        return true;
    }

    public function calculerMontantTTC(Facture $facture): float
    {
        if ($facture->getMontantHT() === null || $facture->getMontantHT() <= 0) {
            throw new \InvalidArgumentException("Le montant HT est requis pour calculer le TTC");
        }
        $tva = $facture->getTva() ?? 0.0;
        return $facture->getMontantHT() * (1 + $tva / 100);
    }
}