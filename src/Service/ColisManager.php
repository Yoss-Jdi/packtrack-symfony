<?php
namespace App\Service;

use App\Entity\Colis;

class ColisManager
{
    private const STATUTS_VALIDES = ['en_attente', 'en_cours', 'livre'];
    private const POIDS_MAX = 1000;

    public function validate(Colis $colis): bool
    {
        // Adresse de destination obligatoire
        if (empty($colis->getAdresseDestination())) {
            throw new \InvalidArgumentException("L'adresse de destination est obligatoire");
        }

        // Adresse de départ obligatoire
        if (empty($colis->getAdresseDepart())) {
            throw new \InvalidArgumentException("L'adresse de départ est obligatoire");
        }

        // Poids obligatoire et positif
        if ($colis->getPoids() === null) {
            throw new \InvalidArgumentException("Le poids est obligatoire");
        }
        if ($colis->getPoids() <= 0) {
            throw new \InvalidArgumentException("Le poids doit être un nombre positif");
        }
        if ($colis->getPoids() >= self::POIDS_MAX) {
            throw new \InvalidArgumentException("Le poids ne peut pas dépasser 1000 kg");
        }

        // Statut valide
        if ($colis->getStatut() !== null && !in_array($colis->getStatut(), self::STATUTS_VALIDES)) {
            throw new \InvalidArgumentException("Le statut '{$colis->getStatut()}' n'est pas valide");
        }

        return true;
    }

    public function calculerMontant(Colis $colis): float
    {
        if ($colis->getPoids() === null || $colis->getPoids() <= 0) {
            throw new \InvalidArgumentException("Le poids est requis pour calculer le montant");
        }
        return $colis->calculerMontant();
    }
}