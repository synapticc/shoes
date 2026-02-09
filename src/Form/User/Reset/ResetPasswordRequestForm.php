<?php

// src/Form/User/Reset/ResetPasswordRequestForm.php

namespace App\Form\User\Reset;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResetPasswordRequestForm extends AbstractType
{
    public function __construct(public readonly Security $security)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $email = '';
        if ($this->security->getUser()) {
            $user = $this->security->getUser();
            $email = $user->getEmail();
        }
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'value' => $email,
                    'hidden' => '',
                ],
            ])
            ->add('send', SubmitType::class, [
                'label' => 'Send email',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
