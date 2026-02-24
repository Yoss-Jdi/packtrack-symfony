<?php

namespace App\Form;

use App\Entity\Technician;
use App\Entity\Vehicule;
use App\Repository\TechnicianRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
                'label' => 'Immatriculation',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '171tun7896',
                ],
                'help' => "Format requis : 3 chiffres, 'tun', puis 4 chiffres (ex: 171tun7896).",
            ])
            ->add('typeVehicule', TextType::class, [
                'label' => 'Type',
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('capacite', NumberType::class, [
                'label' => 'Capacité (kg)',
                'required' => true,
                'scale' => 2,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Disponible' => 'disponible',
                    'En maintenance' => 'en_maintenance',
                    'Hors service' => 'hors_service',
                ],
                'attr' => ['class' => 'form-control', 'id' => 'vehicule_statut'],
            ])
            ->add('problemDescription', TextareaType::class, [
                'label' => 'Description du problème',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'vehicule_problemDescription',
                    'rows' => 4,
                    'placeholder' => 'Décrivez le problème rencontré avec le véhicule...',
                ],
                'help' => 'Cette description sera analysée par IA pour déterminer les actions nécessaires.',
            ])
            ->add('technician', EntityType::class, [
                'class' => Technician::class,
                'choice_label' => fn (Technician $t) => $t->getPrenom() . ' ' . $t->getNom() . ' (' . ucfirst($t->getStatut()) . ')',
                'placeholder' => 'Aucun technicien affecté',
                'required' => false,
                'label' => 'Technicien affecté',
                'attr' => ['class' => 'form-control', 'id' => 'vehicule_technician'],
                'query_builder' => function (TechnicianRepository $repo) use ($options) {
                    $qb = $repo->createQueryBuilder('t');
                    
                    // Get the current vehicle being edited
                    $vehicule = $options['data'];
                    
                    // If editing and vehicle has a technician, include that technician
                    if ($vehicule && $vehicule->getId() && $vehicule->getTechnician()) {
                        $qb->where('t.statut = :disponible')
                           ->orWhere('t.id = :currentTech')
                           ->setParameter('disponible', 'disponible')
                           ->setParameter('currentTech', $vehicule->getTechnician()->getId());
                    } else {
                        // For new vehicles, only show available technicians
                        $qb->where('t.statut = :disponible')
                           ->setParameter('disponible', 'disponible');
                    }
                    
                    return $qb->orderBy('t.nom', 'ASC');
                },
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-primary'],
            ])
        ;

        // Add form event listener to handle technician field based on vehicle status
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $vehicule = $event->getData();
            $form = $event->getForm();

            // If vehicle status is 'disponible', remove technician assignment
            if ($vehicule && $vehicule->getStatut() === 'disponible') {
                $vehicule->setTechnician(null);
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $vehicule = $event->getData();

            // If vehicle status is 'disponible', ensure no technician is assigned
            if ($vehicule && $vehicule->getStatut() === 'disponible') {
                $vehicule->setTechnician(null);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vehicule::class,
        ]);
    }
}
