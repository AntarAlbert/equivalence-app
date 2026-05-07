<?php
// src/Form/ArreteType.php
namespace App\Form;

use App\Entity\Arrete;
use App\Entity\Equivalence;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArreteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Le champ numeroArrete est totalement supprimé (auto-généré)
            ->add('dateArrete', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de l’arrêté',
            ])
            ->add('titre', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('equivalence', EntityType::class, [
                'class' => Equivalence::class,
                'choice_label' => 'numeroDossier',
                'label' => 'Dossier d’équivalence',
            ])
            ->add('articleDispositif', TextareaType::class, [
                'required' => false,
                'label' => 'Article dispositif',
            ])
            ->add('arreteConsiderants', CollectionType::class, [
                'entry_type' => ArreteConsiderantType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Arrete::class,
        ]);
    }
}
