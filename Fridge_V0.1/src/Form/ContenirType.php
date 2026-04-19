<?php

namespace App\Form;

use App\Entity\Contenir;
use App\Form\DataTransformer\IngredientToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire d'une ligne d'ingrédient dans une recette (Contenir).
 *
 * Le champ ingredient est un champ caché (HiddenType) dont la valeur est l'id de l'ingrédient.
 * Un DataTransformer (IngredientToIdTransformer) convertit cet id en entité Ingredient lors de la soumission.
 */
class ContenirType extends AbstractType
{
    public function __construct(private IngredientToIdTransformer $transformer) {}

    /**
     * Construit le formulaire et attache le DataTransformer sur le champ ingredient.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('ingredient', HiddenType::class, [
                'label' => false,
            ])
            ->add('contenirQuantite', NumberType::class, [
                'label'    => false,
                'required' => false,
                'attr'     => [
                    'placeholder' => 'Quantité',
                    'class'       => 'form-control',
                ],
            ])
            ->add('contenirUnite', ChoiceType::class, [
                'label'       => false,
                'required'    => false,
                'placeholder' => 'Unité',
                'choices'     => [
                    'Grammes (g)'        => 'g',
                    'Kilogrammes (kg)'   => 'kg',
                    'Millilitres (ml)'   => 'ml',
                    'Litres (l)'         => 'l',
                    'Cuillère à café'    => 'tsp',
                    'Cuillère à soupe'   => 'tbsp',
                    'Pièce(s)'           => 'pièce(s)',
                    'Gousse(s)'          => 'gousse(s)',
                    'Bouquet'            => 'bouquet',
                    'Pincée'             => 'pincée',
                    'Tranche(s)'         => 'tranche(s)',
                ],
                'attr' => ['class' => 'form-select'],
            ]);

        $builder->get('ingredient')
                ->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contenir::class,
        ]);
    }
}