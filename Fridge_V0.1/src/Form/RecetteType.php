<?php

namespace App\Form;

use App\Entity\Recette;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class RecetteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('recetteLibelle', TextType::class, [
                'label' => 'Titre de la recette',
                'constraints' => [
                    new NotBlank(message: 'Le libellé de la recette ne peut pas être vide.'),
                    new Length(min: 3, max: 255, minMessage: 'Le libellé doit comporter au moins {{ limit }} caractères.', maxMessage: 'Le libellé ne peut pas dépasser {{ limit }} caractères.'),
                ],
            ])
            ->add('recetteDescription', TextareaType::class, [
               'label'    => 'Description',
                'required' => false,
                'attr'     => ['rows' => 4],
            ])
            ->add('recetteDifficulte', ChoiceType::class, [
                'label' => 'Difficulté',
                'choices' => [
                    'Facile' => 'Facile',
                    'Moyen' => 'Moyen',
                    'Difficile' => 'Difficile',
                ],
                'constraints' => [new NotBlank(message: 'La difficulté de la recette ne peut pas être vide.')],    
            ])
            ->add('recettePortion', IntegerType::class, [
                'label'       => 'Nombre de portions',
                'constraints' => [
                    new NotBlank(),
                    new Positive(message: 'Le nombre de portions doit être positif.'),
                ],
            ])
            ->add('recetteTempsPrepa', IntegerType::class, [
                'label'       => 'Temps de préparation (min)',
                'constraints' => [
                    new NotBlank(),
                    new Positive(),
                ],
            ])
            ->add('recetteTempsCuisson', IntegerType::class, [
                'label'       => 'Temps de cuisson (min)',
                'constraints' => [
                    new NotBlank(),
                    new Positive(),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recette::class,
        ]);
    }
}
