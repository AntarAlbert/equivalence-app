<?php
// src/Form/ConfirmationCodeType.php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ConfirmationCodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('code', TextType::class, [
            'label' => false,
            'required' => true,
            'attr' => [
                'autocomplete' => 'one-time-code',
                'inputmode' => 'numeric',
                'maxlength' => 6,
                'class' => 'd-none',
            ],
            'constraints' => [
                new NotBlank(),
                new Length(6),
            ],
        ]);
    }
}
