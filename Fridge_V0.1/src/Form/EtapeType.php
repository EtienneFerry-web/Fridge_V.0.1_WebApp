<?php

namespace App\Form;

use App\Entity\Etape;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

/**
 * Formulaire d'une étape de préparation d'une recette.
 *
 * Utilisé en sous-formulaire CollectionType dans RecetteType.
 * Le numéro d'étape (etapeNumero) est assigné dans RecetteController, pas ici.
 */
class EtapeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('etapeLibelle', TextType::class, [
                'label'       => false,
                'attr'        => ['placeholder' => 'Titre de l\'étape'],
                'constraints' => [new NotBlank(message: 'Le titre de l\'étape est requis.')],
            ])
            ->add('etapeDescription', TextareaType::class, [
                'label'       => false,
                'attr'        => ['placeholder' => 'Description de l\'étape', 'rows' => 2],
                'constraints' => [new NotBlank(message: 'La description de l\'étape est requise.')],
            ])
            ->add('etapeDuree', IntegerType::class, [
                'label'    => false,
                'required' => false,
                'attr'     => ['placeholder' => 'Durée (min)'],
                'constraints' => [new Positive(message: 'La durée doit être positive.')],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Etape::class,
        ]);
    }
}
