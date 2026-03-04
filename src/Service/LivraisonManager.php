<?php
namespace App\Service;

use App\Entity\Livraison;

class LivraisonManager
{
    private const DISTANCE_MAX = 10000;

    public function validate(Livraison $livraison): bool
    {
        // Règle 1 : Date de fin doit être après la date de début
        if ($livraison->getDateFin() !== null && $livraison->getDateDebut() !== null) {
            if ($livraison->getDateFin() <= $livraison->getDateDebut()) {
                throw new \InvalidArgumentException("La date de fin doit être postérieure à la date de début");
            }
        }

        // Règle 2 : Distance positive
        if ($livraison->getDistanceKm() !== null && $livraison->getDistanceKm() <= 0) {
            throw new \InvalidArgumentException("La distance doit être un nombre positif");
        }
        if ($livraison->getDistanceKm() !== null && $livraison->getDistanceKm() >= self::DISTANCE_MAX) {
            throw new \InvalidArgumentException("La distance ne peut pas dépasser 10000 km");
        }

        // Règle 3 : Méthode de paiement valide
        $methodes = ['carte', 'especes', 'virement', 'cheque'];
        if ($livraison->getMethodePaiement() !== null && !in_array($livraison->getMethodePaiement(), $methodes)) {
            throw new \InvalidArgumentException("La méthode de paiement '{$livraison->getMethodePaiement()}' n'est pas valide");
        }

        return true;
    }
}