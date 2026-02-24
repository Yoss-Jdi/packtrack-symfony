<?php

namespace App\Form;

use App\Entity\Utilisateurs;
use App\Entity\Role;
use App\Validator\UniqueEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UtilisateursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control', 
                    'placeholder' => 'exemple@gmail.com'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'L\'email est obligatoire'
                    ]),
                    new Email([
                        'message' => 'Veuillez entrer un email valide (exemple@gmail.com)'
                    ]),
                    new UniqueEmail(), // Ajout de la contrainte d'unicité
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'required' => !$options['is_edit'],
                'attr' => [
                    'class' => 'form-control', 
                    'placeholder' => 'Entrez le mot de passe'
                ],
                'constraints' => !$options['is_edit'] ? [
                    new NotBlank([
                        'message' => 'Le mot de passe est obligatoire'
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères',
                    ]),
                    new Regex([
                        'pattern' => '/[A-Z]/',
                        'message' => 'Le mot de passe doit contenir au moins une lettre majuscule'
                    ]),
                    new Regex([
                        'pattern' => '/[0-9]/',
                        'message' => 'Le mot de passe doit contenir au moins un chiffre'
                    ]),
                ] : [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères',
                    ]),
                    new Regex([
                        'pattern' => '/[A-Z]/',
                        'message' => 'Le mot de passe doit contenir au moins une lettre majuscule'
                    ]),
                    new Regex([
                        'pattern' => '/[0-9]/',
                        'message' => 'Le mot de passe doit contenir au moins un chiffre'
                    ]),
                ],
            ])
            ->add('Nom', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nom'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le nom est obligatoire'
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('Prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Prénom'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le prénom est obligatoire'
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Le prénom doit contenir au moins {{ limit }} caractères',
                    ]),
                    new Callback(function ($prenom, ExecutionContextInterface $context) {
                        $form = $context->getRoot();
                        $nom = $form->get('Nom')->getData();
                        
                        if ($prenom && $nom && strtolower($prenom) === strtolower($nom)) {
                            $context->buildViolation('Le prénom doit être différent du nom')
                                ->addViolation();
                        }
                    }),
                ],
            ])
            ->add('Telephone', TelType::class, [
                'label' => 'Téléphone',
                'required' => true,
                'attr' => ['class' => 'form-control', 'placeholder' => '12345678'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le numéro de téléphone est obligatoire'
                    ]),
                    new Regex([
                        'pattern' => '/^[0-9]{8}$/',
                        'message' => 'Le numéro de téléphone doit contenir exactement 8 chiffres'
                    ]),
                ],
            ])
            ->add('role', EnumType::class, [
                'label' => 'Rôle',
                'class' => Role::class,
                'attr' => ['class' => 'form-control'],
                'choice_label' => function (Role $role) {
                    return match($role) {
                        Role::ADMIN => 'Administrateur',
                        Role::CLIENT => 'Client',
                        Role::ENTREPRISE => 'Entreprise',
                        Role::LIVREUR => 'Livreur',
                    };
                },
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le rôle est obligatoire'
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateurs::class,
            'is_edit' => false,
        ]);
    }
}