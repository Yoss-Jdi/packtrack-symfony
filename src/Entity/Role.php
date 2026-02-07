<?php
namespace App\Entity;

enum Role: string
{
    case ADMIN = 'ADMIN';
    case CLIENT = 'CLIENT';
    case GESTIONNAIRE = 'GESTIONNAIRE';
    case LIVREUR = 'LIVREUR';
}