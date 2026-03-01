<?php
namespace App\Form;

use App\Entity\Facture;
use App\Entity\Colis;
use App\Repository\ColisRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class FactureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('colis', EntityType::class, [
                'class'        => Colis::class,
                'choice_label' => function (Colis $colis) {
                    return 'Colis #' . $colis->getId();
                },
                'placeholder' => 'Choisir un colis *',
                'mapped'      => false,
                'required'    => true,
                'constraints' => [
                    new NotNull([
                        'message' => 'Vous devez sélectionner un colis.'
                    ])
                ],
                'attr' => [
                    'id'    => 'select-colis',
                    'class' => 'form-control'
                ],
                'query_builder' => function (ColisRepository $repo) {
                    return $repo->createQueryBuilder('c')
                        // Jointure avec livraisons
                        ->innerJoin('c.livraisons', 'l')
                        // Livraison doit être terminée
                        ->where('l.statut = :statut')
                        ->setParameter('statut', 'termine')
                        // La livraison ne doit PAS avoir de facture
                        ->andWhere(
                            'l.id NOT IN (
                                SELECT IDENTITY(f.livraison) 
                                FROM App\Entity\Facture f
                            )'
                        )
                        ->orderBy('c.id', 'DESC')
                        ->distinct();
                },
            ])
            ->add('montantLivraison', NumberType::class, [
                'label'    => 'Montant de la livraison (DT)',
                'mapped'   => false,
                'required' => false,
                'attr'     => [
                    'id'          => 'facture_montantLivraison',
                    'readonly'    => true,
                    'placeholder' => 'Sélectionnez un colis',
                    'class'       => 'form-control',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'        => Facture::class,
            'validation_groups' => false,
        ]);
    }
}