<?php

namespace App\Validator;

use App\Repository\UtilisateursRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueEmailValidator extends ConstraintValidator
{
    private UtilisateursRepository $utilisateursRepository;

    public function __construct(UtilisateursRepository $utilisateursRepository)
    {
        $this->utilisateursRepository = $utilisateursRepository;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueEmail) {
            throw new UnexpectedTypeException($constraint, UniqueEmail::class);
        }

        // Si la valeur est vide ou nulle, on ne fait rien (la contrainte NotBlank s'en charge)
        if (null === $value || '' === $value) {
            return;
        }

        // Récupérer l'objet formulaire pour savoir si on est en mode édition
        $form = $this->context->getRoot();
        $utilisateur = $form->getData();

        // Chercher un utilisateur avec cet email dans la base de données
        $existingUser = $this->utilisateursRepository->findOneBy(['Email' => $value]);

        // Si un utilisateur existe avec cet email
        if ($existingUser) {
            // En mode édition, on vérifie que ce n'est pas le même utilisateur
            if ($utilisateur && $utilisateur->getId() && $existingUser->getId() === $utilisateur->getId()) {
                // C'est le même utilisateur, pas d'erreur
                return;
            }

            // L'email existe déjà pour un autre utilisateur, on ajoute une violation
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}