<?php

namespace App\Form;

use App\Entity\Diplome;
use App\Entity\Equivalence;
use App\Entity\Pays;
use App\Repository\DiplomeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class EquivalenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ==================== IDENTITÉ ====================
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Nom du candidat', 'class' => 'form-control text-uppercase', 'autocomplete' => 'family-name'],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['placeholder' => 'Prénom du candidat', 'class' => 'form-control', 'autocomplete' => 'given-name'],
            ])
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de naissance',
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('lieuNaissance', TextType::class, [
                'label' => 'Lieu de naissance',
                'required' => false,
                'attr' => ['placeholder' => 'Ville et pays de naissance', 'class' => 'form-control'],
            ])

            // ==================== COORDONNÉES & NATIONALITÉ ====================
            ->add('email', TextType::class, [
                'label' => 'Adresse email',
                'required' => true,
                'attr' => ['class' => 'form-control', 'autocomplete' => 'email'],
            ])
            ->add('nationalite', EntityType::class, [
                'class' => Pays::class,
                'choice_label' => 'nomFrFr',
                'placeholder' => 'Sélectionnez la nationalité',
                'label' => 'Nationalité',
                'required' => true,
                'attr' => ['class' => 'form-select'],
            ])
->add('cni', TextType::class, [
    'label' => 'Numéro CNI',
    'required' => false,
    'constraints' => [
        new Assert\Length([
            'min' => 12,
            'max' => 12,
            'exactMessage' => 'Le numéro CNI doit contenir exactement 12 chiffres.',
        ]),
        new Assert\Regex([
            'pattern' => '/^\d{12}$/',
            'message' => 'Le numéro CNI ne doit contenir que des chiffres (12 chiffres).',
        ]),
    ],
    'attr' => [
        'class' => 'form-control',
        'placeholder' => '123456789012',
        'maxlength' => 15,           // pour l’affichage avec espaces (12 chiffres + 3 espaces)
        'data-mask' => 'cni',        // déjà présent
    ],
])
            ->add('cniDateDelivrance', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de délivrance du CNI',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('cniLieuDelivrance', TextType::class, [
                'label' => 'Lieu de délivrance du CNI',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('cniDateDuplicata', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date du duplicata',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('cniLieuDuplicata', TextType::class, [
                'label' => 'Lieu de délivrance du duplicata',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])

            // ==================== EMPLOI ====================
            ->add('emploi', ChoiceType::class, [
                'label' => 'Situation professionnelle',
                'placeholder' => 'Sélectionnez une option',
                'choices' => [
                    'Chômeur'       => 'chomeur',
                    'Dans le privé' => 'prive',
                    'Fonctionnaire' => 'fonctionnaire',
                ],
                'required' => false,
                'attr' => ['class' => 'form-select'],
            ])
            ->add('matricule', TextType::class, [
                'label' => 'Matricule',
                'required' => false,
                'attr' => ['placeholder' => 'Matricule agent (facultatif)', 'class' => 'form-control'],
            ])

            // ==================== DIPLÔME ====================
           // ==================== DIPLÔME ====================
->add('diplomeReference', EntityType::class, [
    'class' => Diplome::class,
    'query_builder' => function (DiplomeRepository $repo) {
        return $repo->createQueryBuilder('d')
            ->leftJoin('d.etablissement', 'e')
            ->leftJoin('e.pays', 'p')
            ->addSelect('e', 'p')
            ->orderBy('d.titre', 'ASC');
    },
    'choice_label' => function (?Diplome $diplome): string {
        if (!$diplome) return '';
        $etab = $diplome->getEtablissement();
        $pays = $etab?->getPays()?->getNomFrFr();
        return sprintf(
            '%s - %s%s',
            $diplome->getTitre(),
            $etab?->getNom() ?? 'Établissement inconnu',
            $pays ? " ($pays)" : ''
        );
    },
    'placeholder' => 'Choisissez un diplôme',
    'label' => 'Diplôme de référence',
    'required' => true,
    'attr' => ['class' => 'form-select'],

    // === AJOUT IMPORTANT POUR LE REMPLISSAGE AUTOMATIQUE ===
    'choice_attr' => function (?Diplome $diplome) {
        if (!$diplome) {
            return [];
        }

        $organisme = '';
        $pays = '';

        if ($etablissement = $diplome->getEtablissement()) {
            $organisme = $etablissement->getNom() ?? '';
            if ($etablissement->getPays()) {
                $pays = $etablissement->getPays()->getNomFrFr() ?? '';
            }
        }

        return [
            'data-organisme' => $organisme,
            'data-pays'      => $pays,
        ];
    },
])
            ->add('universite', TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Université / Organisme',
                'attr' => ['readonly' => true, 'class' => 'form-control bg-light'],
            ])
            ->add('pays', TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Pays',
                'attr' => ['readonly' => true, 'class' => 'form-control bg-light'],
            ])

            // ==================== OBSERVATIONS ====================
            ->add('observation', TextareaType::class, [
                'label' => 'Observations complémentaires',
                'required' => false,
                'attr' => ['rows' => 5, 'class' => 'form-control'],
            ])

            // ==================== DOCUMENTS ====================
            ->add('diplomaFile', FileType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Diplôme scanné (PDF)',
                'constraints' => [new Assert\File(['maxSize' => '6M', 'mimeTypes' => ['application/pdf']])],
                'attr' => ['class' => 'form-control', 'accept' => 'application/pdf'],
            ])
            ->add('transcriptFile', FileType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Relevé de notes (PDF)',
                'constraints' => [new Assert\File(['maxSize' => '6M', 'mimeTypes' => ['application/pdf']])],
                'attr' => ['class' => 'form-control', 'accept' => 'application/pdf'],
            ])
            ->add('identityFile', FileType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Pièce d\'identité (PDF)',
                'constraints' => [new Assert\File(['maxSize' => '6M', 'mimeTypes' => ['application/pdf']])],
                'attr' => ['class' => 'form-control', 'accept' => 'application/pdf'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Equivalence::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
        ]);
    }
}
