<?php
// src/Form/DiplomeType.php

namespace App\Form;

use App\Entity\Diplome;
use App\Entity\Etablissement;
use App\Repository\EtablissementRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class DiplomeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            // =====================================================
            // TITRE
            // =====================================================

            ->add('titre', TextType::class, [
                'label' => 'Titre du diplôme',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le titre est obligatoire.',
                    ]),
                    new Length(
                        max: 255,
                        maxMessage: 'Le titre ne doit pas dépasser 255 caractères.'
                    ),
                ],
                'attr' => [
                    'placeholder' => 'Ex : Master en Informatique',
                    'class' => 'form-control',
                    'autocomplete' => 'off',
                ],
            ])

            // =====================================================
            // ETABLISSEMENT
            // =====================================================

            ->add('etablissement', EntityType::class, [
                'class' => Etablissement::class,

                'query_builder' => function (EtablissementRepository $repository) {
                    return $repository
                        ->createQueryBuilder('e')
                        ->leftJoin('e.pays', 'p')
                        ->addSelect('p')
                        ->orderBy('e.nom', 'ASC');
                },

                'choice_label' => function (?Etablissement $etablissement): string {

                    if (!$etablissement) {
                        return '';
                    }

                    $pays =
                        $etablissement->getPays()?->getNomFrFr();

                    return $pays
                        ? sprintf(
                            '%s (%s)',
                            $etablissement->getNom(),
                            $pays
                        )
                        : $etablissement->getNom();
                },

                'choice_value' => 'id',

                'placeholder' => 'Sélectionner un établissement',

                'label' => 'Établissement / Université',

                'required' => true,

                'invalid_message' => 'Établissement invalide.',

                'attr' => [
                    'class' => 'form-select',
                    'data-placeholder' => 'Choisir un établissement',
                ],
            ])

            // =====================================================
            // ORGANISME (LECTURE SEULE)
            // =====================================================
            //
            // IMPORTANT :
            // Le champ organisme n’existe plus réellement
            // comme propriété Doctrine persistée.
            //
            // Donc :
            // - mapped => false
            // - data => valeur calculée depuis getOrganisme()
            //
            // =====================================================

            ->add('organisme', TextType::class, [
                'label' => 'Organisme (historique)',
                'mapped' => false,
                'required' => false,

                'data' => $builder->getData()?->getOrganisme(),

                'constraints' => [
                    new Length(
                        max: 255,
                        maxMessage: 'Maximum 255 caractères.'
                    ),
                ],

                'attr' => [
                    'class' => 'form-control bg-light',
                    'placeholder' => 'Généré automatiquement',
                    'readonly' => true,
                ],

                'help' => 'Champ généré automatiquement à partir de l’établissement sélectionné.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Diplome::class,
            'csrf_protection' => true,
        ]);
    }
}
