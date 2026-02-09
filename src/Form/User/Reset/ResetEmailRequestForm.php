<?php

// src/Form/User/Reset/ResetEmailRequestForm.php

namespace App\Form\User\Reset;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetEmailRequestForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'attr' => [
                        // 'autocomplete' => 'on',
                        // 'class' => 'form-style',
                        // 'placeholder' => 'Enter your current password',
                        // 'autofocus' => 'on'
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter a password',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Your password should be at least {{ limit }} characters',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                    ],
                    // 'label' => 'Current password',
                ],
                'second_options' => [
                    'attr' => [
                        // 'autocomplete' => 'on',
                        // 'class' => 'form-style',
                        // 'placeholder' => 'Repeat your current password',
                        // 'autofocus' => 'on'
                    ],
                    'label' => 'Re-enter your password',
                ],
                'invalid_message' => 'The password fields must match.',
                // Instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
