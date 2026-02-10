<?php
namespace App\Entity;

enum Role: string
{
    case ADMIN = 'ADMIN';
    case CLIENT = 'CLIENT';
    case ENTREPRISE = 'ENTREPRISE';
    case LIVREUR = 'LIVREUR';
}