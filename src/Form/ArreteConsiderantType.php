<?php
// src/Form/ArreteConsiderantType.php

namespace App\Form;

use App\Entity\ArreteConsiderant;
use App\Entity\Considerant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArreteConsiderantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('considerant', EntityType::class, [
                'class' => Considerant::class,
                'choice_label' => function (Considerant $considerant) {
                    return sprintf(
                        '%s %s (%s)',
                        $considerant->getType(),
                        $considerant->getReference(),
                        $considerant->getDate() ? $considerant->getDate()->format('d/m/Y') : ''
                    );
                },
                'label' => 'Considérant',
                'placeholder' => 'Choisissez un considérant',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('ordre', IntegerType::class, [
                'label' => 'Ordre d’apparition',
                'attr' => ['min' => 0, 'class' => 'form-control'],
                'help' => 'Ordre dans la liste des « Vu que » (0 = premier)',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ArreteConsiderant::class,
        ]);
    }
}
