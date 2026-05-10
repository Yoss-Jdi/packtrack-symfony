<?php

namespace App\Form;

use App\Entity\Technician;
use App\Entity\Vehicule;
use App\Repository\TechnicianRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VehiculeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('marque', TextType::class, [
                'label' => 'Marque',
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('modele', TextType::class, [
                'label' => 'Modèle',
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('immatriculation', TextType::class, [
                'label' => 'Matricule',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '171tun7896',
                ],
                'help' => "Format requis : 3 chiffres, 'tun', puis 4 chiffres (ex: 171tun7896).",
            ])
            ->add('couleur', TextType::class, [
                'label' => 'Couleur',
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('prixLocation', NumberType::class, [
                'label' => 'Prix de location (DT)',
                'required' => true,
                'scale' => 2,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('disponible', CheckboxType::class, [
                'label' => 'Disponible',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ])
            ->add('technician', EntityType::class, [
                'class' => Technician::class,
                'choice_label' => fn (Technician $t) => $t->getPrenom() . ' ' . $t->getNom(),
                'placeholder' => 'Aucun technicien affecté',
                'required' => false,
                'label' => 'Technicien affecté',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-primary'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vehicule::class,
        ]);
    }
}