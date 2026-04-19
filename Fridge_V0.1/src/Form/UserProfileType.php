<?php

namespace App\Form;

use App\Entity\Regime;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Formulaire d'édition du profil utilisateur.
 *
 * Permet de modifier les informations personnelles, les régimes alimentaires préférés et optionnellement le mot de passe.
 * Le champ newPassword est non mappé (mapped: false) et non requis : le hachage n'est effectué que s'il est renseigné.
 */
class UserProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('strEmail', EmailType::class, [
                'label' => 'Email',
            ])
            ->add('strName', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('strFirstname', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('strUsername', TextType::class, [
                'label' => 'Pseudo',
            ])

            ->add('regimes', EntityType::class, [
                'class'        => Regime::class,
                'choice_label' => 'regimeLibelle',
                'multiple'     => true,
                'expanded'     => true,
                'label'        => 'Mes régimes alimentaires',
                'required'     => false,
            ])

            ->add('newPassword', RepeatedType::class, [
                'type'           => PasswordType::class,
                'mapped'         => false,           
                'required'       => false,           
                'first_options'  => ['label' => 'Nouveau mot de passe'],
                'second_options' => ['label' => 'Confirmer le mot de passe'],
                'constraints'    => [new Length(min: 8)],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
