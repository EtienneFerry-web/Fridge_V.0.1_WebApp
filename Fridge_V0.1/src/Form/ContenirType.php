<?php

namespace App\Form;

use App\Entity\Contenir;
use App\Entity\Ingredient;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContenirType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ingredient', EntityType::class, [
                'class' => Ingredient::class,
                'choice_label' => 'ingredientLibelle',
                'label' => false,
                'placeholder' => 'Sélectionnez un ingrédient',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('contenirQuantite', NumberType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => 'Quantité', 'class' => 'form-control'],
            ])
            ->add('contenirUnite', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => 'Unité (ex: g, ml, pièce ...)', 'class' => 'form-control'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contenir::class,
        ]);
    }
}
