<?php

namespace App\Form;

use App\Entity\FactureMaintenance;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FactureMaintenanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numero', TextType::class, [
                'label' => 'Numéro de facture',
                'attr' => [
                    'placeholder' => 'Ex: MAINT-2024-001',
                    'class' => 'form-control'
                ]
            ])
            ->add('montantHT', NumberType::class, [
                'label' => 'Montant HT (TND)',
                'attr' => [
                    'placeholder' => '0.00',
                    'class' => 'form-control',
                    'step' => '0.01'
                ]
            ])
            ->add('tauxTVA', NumberType::class, [
                'label' => 'Taux TVA (%)',
                'attr' => [
                    'placeholder' => '19',
                    'class' => 'form-control',
                    'step' => '0.01'
                ],
                'data' => '19'
            ])
            ->add('descriptionTravaux', TextareaType::class, [
                'label' => 'Description des travaux effectués',
                'attr' => [
                    'placeholder' => 'Décrivez les travaux de maintenance effectués...',
                    'class' => 'form-control',
                    'rows' => 4
                ]
            ])
            ->add('fournisseur', TextType::class, [
                'label' => 'Fournisseur / Garage',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Nom du fournisseur ou garage',
                    'class' => 'form-control'
                ]
            ])
            ->add('pieceChangees', TextareaType::class, [
                'label' => 'Pièces changées',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Liste des pièces remplacées...',
                    'class' => 'form-control',
                    'rows' => 3
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FactureMaintenance::class,
        ]);
    }
}
