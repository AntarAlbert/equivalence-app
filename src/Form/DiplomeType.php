<?php

namespace App\Form;

use App\Entity\Diplome;
use App\Entity\Etablissement;
use App\Enum\DiplomeDomaine;
use App\Enum\DiplomeNiveau;
use App\Repository\EtablissementRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class DiplomeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEtablissement = $options['is_etablissement'] ?? false;

        $builder
            ->add('titre', TextType::class, [
                'label' => 'Intitulé complet du diplôme',
                'constraints' => [new NotBlank()],
                'attr' => [
                    'placeholder' => 'Ex : Brevet d\'Études du Premier Cycle',
                    'class' => 'form-control',
                ],
            ])

            // ====================== DOMAINE ======================
            ->add('domaine', EnumType::class, [
                'class' => DiplomeDomaine::class,
                'label' => 'Domaine d’études',
                'placeholder' => 'Sélectionnez un domaine',
                'choice_label' => fn(DiplomeDomaine $domaine) => $domaine->getLabel(),
                'required' => true,
                'constraints' => [new NotBlank()],
            ])

            // ====================== NIVEAU ======================
            ->add('niveau', EnumType::class, [
                'class' => DiplomeNiveau::class,
                'label' => 'Niveau',
                'placeholder' => 'Sélectionnez le niveau',
                'choice_label' => fn(DiplomeNiveau $niveau) => $niveau->getLabel(),
                'required' => true,
                'constraints' => [new NotBlank()],
            ])

            // ====================== DURÉE ======================
            ->add('duree', IntegerType::class, [
                'label' => 'Durée en années',
                'required' => true,
                'constraints' => [new PositiveOrZero()],
                'attr' => [
                    'min' => 0,
                    'max' => 10,
                    'class' => 'form-control',
                ],
                'help' => '0 = formation sans durée annuelle fixe (ex: Brevet, Bac)',
            ])

            // ====================== ETABLISSEMENT ======================
            ->add('etablissement', EntityType::class, [
                'class' => Etablissement::class,
                'query_builder' => fn(EtablissementRepository $repo) =>
                    $repo->createQueryBuilder('e')
                         ->leftJoin('e.pays', 'p')
                         ->addSelect('p')
                         ->orderBy('e.nom', 'ASC'),
                'choice_label' => function (Etablissement $e): string {
                    $pays = $e->getPays()?->getNomFrFr();
                    return $pays ? "{$e->getNom()} ({$pays})" : $e->getNom();
                },
                'placeholder' => 'Sélectionner un établissement',
                'label' => 'Établissement',
                'required' => true,
                'disabled' => $isEtablissement,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Diplome::class,
        ]);

        $resolver->setDefined('is_etablissement');
        $resolver->setAllowedTypes('is_etablissement', 'bool');
        $resolver->setDefault('is_etablissement', false);
    }
}
