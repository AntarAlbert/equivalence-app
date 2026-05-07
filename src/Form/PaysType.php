<?php

namespace App\Form;

use App\Entity\Pays;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

final class PaysType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {

        $builder
            ->add('code', IntegerType::class, [
                'label' => 'Code numérique',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'max' => 999,
                ],
                'constraints' => [
                    new NotBlank(),
                    new Positive(),
                ],
            ])

            ->add('alpha2', TextType::class, [
                'label' => 'Code alpha-2',
                'attr' => [
                    'class' => 'form-control text-uppercase',
                    'maxlength' => 2,
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(
                        min: 2,
                        max: 2
                    ),
                ],
            ])

            ->add('alpha3', TextType::class, [
                'label' => 'Code alpha-3',
                'attr' => [
                    'class' => 'form-control text-uppercase',
                    'maxlength' => 3,
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(
                        min: 3,
                        max: 3
                    ),
                ],
            ])

            ->add('nomFrFr', TextType::class, [
                'label' => 'Nom français',
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 45,
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(max: 45),
                ],
            ])

            ->add('nomEnGb', TextType::class, [
                'label' => 'Nom anglais',
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 45,
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(max: 45),
                ],
            ]);
    }

    public function configureOptions(
        OptionsResolver $resolver
    ): void {

        $resolver->setDefaults([
            'data_class' => Pays::class,
            'csrf_protection' => true,
        ]);
    }
}
