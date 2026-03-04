<?php
namespace App\Service;

use App\Entity\Publication;

class PublicationManager
{
    private const STATUTS_VALIDES = ['active', 'inactive'];
    private const TITRE_MAX = 200;
    private const CONTENU_MAX = 10000;

    public function validate(Publication $publication): bool
    {
        // Règle 1 : Titre obligatoire
        if (empty(trim($publication->getTitre()))) {
            throw new \InvalidArgumentException("Le titre est obligatoire");
        }

        // Règle 2 : Titre max 200 caractères
        if (strlen($publication->getTitre()) > self::TITRE_MAX) {
            throw new \InvalidArgumentException("Le titre ne doit pas dépasser 200 caractères");
        }

        // Règle 3 : Contenu max 10000 caractères
        if ($publication->getContenu() !== null && strlen($publication->getContenu()) > self::CONTENU_MAX) {
            throw new \InvalidArgumentException("Le contenu est trop long");
        }

        // Règle 4 : Statut valide
        if (!in_array($publication->getStatut(), self::STATUTS_VALIDES)) {
            throw new \InvalidArgumentException("Le statut '{$publication->getStatut()}' n'est pas valide");
        }

        return true;
    }

    public function isActive(Publication $publication): bool
    {
        return $publication->getStatut() === 'active';
    }
}