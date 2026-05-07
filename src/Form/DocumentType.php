<?php

namespace App\Form;

use App\Entity\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

class DocumentType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {

        $builder

            ->add('type', ChoiceType::class, [

                'choices' => [

                    'Diplôme' => 'DIPLOME',

                    'Relevé de notes' => 'RELEVE',

                    'CIN' => 'CIN',

                ],

            ])

            ->add('file', FileType::class, [

                'mapped' => false,

            ]);
    }
}