<?php
namespace App\Form;

use App\Entity\Colis;
use App\Entity\Utilisateurs; // ✅ CORRECTION
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Repository\UtilisateursRepository;

class ColisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextareaType::class, [
                'label' => 'Description du colis',
                'required' => true,
                'attr' => [
                    'rows' => 3,
                    'novalidate' => 'novalidate'
                ],
                'help' => 'Entre 5 et 500 caractères (validation côté serveur)'
            ])
            ->add('articles', TextareaType::class, [
                'label' => 'Liste des articles',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Ex: 2 cartons, 1 palette...',
                    'novalidate' => 'novalidate'
                ],
                'help' => 'Maximum 1000 caractères (validation côté serveur)'
            ])
            ->add('adresseDestination', TextareaType::class, [
                'label' => 'Adresse de destination',
                'required' => true,
                'attr' => [
                    'rows' => 2,
                    'novalidate' => 'novalidate'
                ],
                'help' => 'Entre 10 et 500 caractères (validation côté serveur)'
            ])
            ->add('poids', NumberType::class, [
                'label' => 'Poids (kg) *',
                'required' => true,
                'attr' => [
                    'step' => '0.01',
                    'placeholder' => 'Ex: 15.5',
                    'novalidate' => 'novalidate'
                ],
                'help' => 'Obligatoire - Utilisé pour calculer le montant. Doit être positif et < 1000 kg'
            ])
            ->add('dimensions', TextType::class, [
                'label' => 'Dimensions',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: 50x40x30 cm',
                    'novalidate' => 'novalidate'
                ],
                'help' => 'Chiffres, espaces et "x" uniquement (validation côté serveur)'
            ])
            ->add('destinataire', EntityType::class, [
                'class' => Utilisateurs::class, // ✅ CORRECTION
                'choice_label' => function(Utilisateurs $user) { // ✅ CORRECTION
                    return $user->getPrenom() . ' ' . $user->getNom() . ' (' . $user->getEmail() . ')';
                },
                'label' => 'Destinataire',
                'placeholder' => 'Sélectionnez un destinataire',
                'required' => true,
                'attr' => ['novalidate' => 'novalidate'],
                'query_builder' => function (UtilisateursRepository $repo) {
                    return $repo->createQueryBuilder('u')
                        ->where('u.role = :role')
                        ->setParameter('role', 'Client')
                        ->orderBy('u.Nom', 'ASC');
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Colis::class,
            'attr' => ['novalidate' => 'novalidate'], 
        ]);
    }
}