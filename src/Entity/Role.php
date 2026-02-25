<?php
namespace App\Entity;

enum Role: string
{
    case ADMIN = 'ADMIN';
    case CLIENT = 'CLIENT';
    case ENTREPRISE = 'ENTREPRISE';

    case LIVREUR = 'Livreur';   
    case EXPEDITEUR = 'expediteur';      // ✅ AJOUTER
    case DESTINATAIRE = 'destinataire';  // ✅ AJOUTER
}