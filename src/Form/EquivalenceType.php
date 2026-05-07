<?php
// src/Form/EquivalenceType.php

namespace App\Form;

use App\Entity\Diplome;
use App\Entity\Equivalence;
use App\Entity\Pays;
use App\Repository\DiplomeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class EquivalenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Nom du candidat'],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['placeholder' => 'Prénom du candidat'],
            ])
            ->add('email', TextType::class, [
                'label' => 'Adresse email',
                'required' => true,
                'attr' => ['placeholder' => 'exemple@domaine.com', 'autocomplete' => 'email'],
            ])
            ->add('nationalite', EntityType::class, [
                'class' => Pays::class,
                'choice_label' => 'nomFrFr',
                'placeholder' => 'Sélectionnez la nationalité',
                'label' => 'Nationalité',
                'required' => false,
                'attr' => ['class' => 'form-select'],
            ])
            ->add('diplomeReference', EntityType::class, [
                'class' => Diplome::class,
                'query_builder' => function (DiplomeRepository $repo) {
                    return $repo->createQueryBuilder('d')
                        ->leftJoin('d.etablissement', 'e')
                        ->leftJoin('e.pays', 'p')
                        ->addSelect('e', 'p');
                },
                'choice_label' => function (?Diplome $diplome): string {
                    if (!$diplome) {
                        return '';
                    }
                    $titre = $diplome->getTitre();
                    $etablissement = $diplome->getEtablissement();
                    $nomEtab = $etablissement ? $etablissement->getNom() : 'Établissement inconnu';
                    $pays = $etablissement && $etablissement->getPays() ? $etablissement->getPays()->getNomFrFr() : '';
                    return sprintf('%s - %s%s', $titre, $nomEtab, $pays ? " ($pays)" : '');
                },
                'placeholder' => 'Choisissez un diplôme',
                'label' => 'Diplôme de référence',
                'required' => true,
                'choice_attr' => function (?Diplome $diplome): array {
                    if (!$diplome) {
                        return [];
                    }
                    $etablissement = $diplome->getEtablissement();
                    $organisme = $etablissement ? $etablissement->getNom() : ($diplome->getOrganisme() ?? '');
                    $paysObj = $etablissement?->getPays();
                    $pays = $paysObj ? $paysObj->getNomFrFr() : '';
                    return [
                        'data-organisme' => $organisme,
                        'data-pays'      => $pays,
                    ];
                },
            ])
            ->add('universite', TextType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => ['readonly' => true, 'class' => 'form-control-plaintext'],
                'label' => 'Université / Organisme',
            ])
            ->add('pays', TextType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => ['readonly' => true],
                'label' => 'Pays du diplôme',
            ])
            ->add('observation', TextareaType::class, [
                'required' => false,
                'label' => 'Observations',
                'attr' => ['rows' => 6, 'placeholder' => 'Observations complémentaires...'],
            ])
            ->add('diplomaFile', FileType::class, [
                'mapped' => false,
                'required' => true,
                'constraints' => [new File(['maxSize' => '6M', 'mimeTypes' => ['application/pdf']])],
                'label' => 'Diplôme scanné (PDF)',
            ])
            ->add('transcriptFile', FileType::class, [
                'mapped' => false,
                'required' => true,
                'constraints' => [new File(['maxSize' => '6M', 'mimeTypes' => ['application/pdf']])],
                'label' => 'Relevés de notes (PDF)',
            ])
            ->add('identityFile', FileType::class, [
                'mapped' => false,
                'required' => true,
                'constraints' => [new File(['maxSize' => '6M', 'mimeTypes' => ['application/pdf']])],
                'label' => 'Pièce d\'identité (PDF)',
            ])
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Date de naissance',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('cni', TextType::class, [
            'label' => 'Numéro CNI',
            'required' => false,
            'constraints' => [
                new Length([
                    'min' => 12,
                    'max' => 12,
                    'exactMessage' => 'Le numéro CNI doit contenir exactement 12 chiffres.',
                ]),
                new Regex([
                    'pattern' => '/^\d{12}$/',
                    'message' => 'Le numéro CNI ne doit contenir que des chiffres (12 chiffres).',
                ]),
            ],
            'attr' => [
                'class' => 'form-control',
                'placeholder' => 'ex: 123456789012',
                'maxlength' => 12,
                'pattern' => '\d{12}',      // validation HTML5 : uniquement 12 chiffres
                'title' => '12 chiffres (0-9)',
                'inputmode' => 'numeric',
            ],
        ])
        ->add('cniDateDelivrance', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date de délivrance du CNI',
            'required' => false,
            'attr' => ['class' => 'form-control', 'data-cin-field' => 'cni-date']
        ])
        ->add('cniLieuDelivrance', TextType::class, [
            'label' => 'Lieu de délivrance du CNI',
            'required' => false,
            'attr' => ['class' => 'form-control', 'data-cin-field' => 'cni-lieu']
        ])
        ->add('cniDateDuplicata', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date du duplicata (si renouvellement)',
            'required' => false,
            'attr' => ['class' => 'form-control', 'data-cin-field' => 'cni-duplicata-date']
        ])
        ->add('cniLieuDuplicata', TextType::class, [
            'label' => 'Lieu du duplicata',
            'required' => false,
            'attr' => ['class' => 'form-control', 'data-cin-field' => 'cni-duplicata-lieu']
        ]);
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
