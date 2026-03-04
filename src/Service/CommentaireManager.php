<?php
namespace App\Service;

use App\Entity\Commentaire;

class CommentaireManager
{
    private const CONTENU_MIN = 2;
    private const CONTENU_MAX = 2000;

    public function validate(Commentaire $commentaire): bool
    {
        // Règle 1 : Contenu obligatoire
        if (empty(trim($commentaire->getContenu()))) {
            throw new \InvalidArgumentException("Le commentaire est obligatoire");
        }

        // Règle 2 : Contenu min 2 caractères
        if (strlen(trim($commentaire->getContenu())) < self::CONTENU_MIN) {
            throw new \InvalidArgumentException("Le commentaire doit contenir au moins 2 caractères");
        }

        // Règle 3 : Contenu max 2000 caractères
        if (strlen($commentaire->getContenu()) > self::CONTENU_MAX) {
            throw new \InvalidArgumentException("Le commentaire ne doit pas dépasser 2000 caractères");
        }

        return true;
    }
}