<?php

namespace App\Service;

use App\Entity\Utilisateurs;
use InvalidArgumentException;

class UtilisateursManager
{
    public function validate(Utilisateurs $user): bool
    {
        if ('' === trim($user->getEmail())) {
            throw new InvalidArgumentException('Email cannot be empty');
        }

        if (strlen($user->getMotDePasse()) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters long');
        }

        return true;
    }
}
