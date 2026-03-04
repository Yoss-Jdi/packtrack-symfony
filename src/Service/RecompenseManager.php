<?php
namespace App\Service;

use App\Entity\Recompense;

class RecompenseManager
{
    private const TYPES_VALIDES = ['badge', 'bonus', 'reduction', 'cadeau'];

    public function validate(Recompense $recompense): bool
    {
        // Règle 1 : Type valide
        if ($recompense->getType() !== null && !in_array($recompense->getType(), self::TYPES_VALIDES)) {
            throw new \InvalidArgumentException("Le type '{$recompense->getType()}' n'est pas valide");
        }

        // Règle 2 : Valeur doit être positive
        if ($recompense->getValeur() !== null && $recompense->getValeur() <= 0) {
            throw new \InvalidArgumentException("La valeur de la récompense doit être positive");
        }

        // Règle 3 : Seuil doit être positif
        if ($recompense->getSeuil() !== null && $recompense->getSeuil() <= 0) {
            throw new \InvalidArgumentException("Le seuil doit être un entier positif");
        }

        // Règle 4 : Date d'obtention ne peut pas être dans le futur
        if ($recompense->getDateObtention() !== null && $recompense->getDateObtention() > new \DateTime()) {
            throw new \InvalidArgumentException("La date d'obtention ne peut pas être dans le futur");
        }

        return true;
    }
}