<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueEmail extends Constraint
{
    public string $message = 'Cet email "{{ value }}" est déjà utilisé par un autre utilisateur.';
    
    public function validatedBy(): string
    {
        return static::class.'Validator';
    }
}