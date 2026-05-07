<?php

namespace App\Form;

use App\Entity\Diplome;
use App\Entity\RegleEquivalence;
use App\Enum\Cadre;
use App\Enum\Echelle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegleEquivalenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('diplome', EntityType::class, [
                'class' => Diplome::class,
                'choice_label' => fn(Diplome $d) => $d->getDisplayName(),
                'placeholder' => 'Choisir un diplôme',
                'label' => 'Diplôme',
            ])

            ->add('cadre', ChoiceType::class, [
                'choices' => Cadre::cases(),
                'choice_label' => fn(Cadre $c) => $c->getLabel(),
                'label' => 'Cadre',
            ])

            ->add('echelle', ChoiceType::class, [
                'choices' => Echelle::cases(),
                'choice_label' => fn(Echelle $e) => $e->getLabel(),
                'label' => 'Échelle',
            ])

            ->add('categorie', TextType::class, [
                'label' => 'Catégorie',
                'attr' => [
                    'placeholder' => 'Ex: VII'
                ]
            ])

            ->add('bonification', IntegerType::class, [
                'label' => 'Bonification',
                'attr' => [
                    'min' => 0
                ]
            ])

            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date début',
            ])

            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Date fin',
            ])

            ->add('actif', CheckboxType::class, [
                'required' => false,
                'label' => 'Règle active',
            ])

            ->add('texteReference', TextareaType::class, [
                'required' => false,
                'label' => 'Texte de référence',
                'attr' => [
                    'rows' => 5
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RegleEquivalence::class,
        ]);
    }
}
