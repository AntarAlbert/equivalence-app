<?php
// src/Form/ConsiderantType.php

namespace App\Form;

use App\Entity\Considerant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsiderantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => array_flip(Considerant::TYPES),
                'label' => 'Type',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('reference', TextType::class, [
                'label' => 'Référence',
                'attr' => ['placeholder' => 'Ex: n° 2003-011 du 03 septembre 2003'],
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Date',
            ])
            ->add('portant', TextType::class, [
                'label' => 'Portant',
                'attr' => ['placeholder' => 'Objet du texte'],
            ])
            ->add('extrait', TextareaType::class, [
                'required' => false,
                'label' => 'Extrait (optionnel)',
                'attr' => ['rows' => 2],
            ])
            ->add('ordre', IntegerType::class, [
                'label' => 'Ordre d’affichage',
                'attr' => ['min' => 0],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Considerant::class,
        ]);
    }
}
