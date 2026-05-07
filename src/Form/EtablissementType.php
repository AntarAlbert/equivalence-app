<?php
// src/Form/EtablissementType.php

namespace App\Form;

use App\Entity\Etablissement;
use App\Entity\Pays;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class EtablissementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('nom', TextType::class, [
                'label' => 'Nom de l’établissement',
                'constraints' => [
                    new NotBlank(),
                    new Length(max: 255),
                ],
            ])

            ->add('pays', EntityType::class, [
                'class' => Pays::class,
                'choice_label' => 'nomFrFr',
                'placeholder' => 'Choisir un pays',
                'required' => false,
                'label' => 'Pays',
                'invalid_message' => 'Valeur invalide',   // ← ajout
                'choice_value' => 'id',                   // ← ajout
            ])

            ->add('ville', TextType::class, [
                'required' => false,
                'label' => 'Ville',
                'constraints' => [
                    new Length(max: 150),
                ],
            ])

            ->add('type', ChoiceType::class, [
                'required' => false,
                'label' => 'Type',
                'placeholder' => 'Choisir un type',
                'choices' => [
                    'Université' => 'universite',
                    'École' => 'ecole',
                    'Institut' => 'institut',
                    'Centre' => 'centre',
                    'Académie' => 'academie',
                    'Autre' => 'autre',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Etablissement::class,
        ]);
    }
}
