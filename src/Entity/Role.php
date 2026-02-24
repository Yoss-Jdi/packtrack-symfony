<?php
namespace App\Entity;

enum Role: string
{
    case ADMINISTRATEUR = 'Administrateur';
    case CLIENT = 'Client';
    case LIVREUR = 'Livreur';
    case ENTREPRISE = 'Entreprise';
    case GESTIONNAIRE = 'Gestionnaire';
}