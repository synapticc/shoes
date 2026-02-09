<?php

// src/Form/User/UserAdminForm.php

namespace App\Form\User;

use App\Entity\User\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserAdminForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // 'ROLE_USER' is added to all users (admin members and customers)
            ->add('roles', ChoiceType::class, [
                'multiple' => true,
                'required' => false,
                'label' => false,

                'choices' => [
                    'ROLE_USER' => 'ROLE_USER',
                    'ROLE_ADMIN' => 'ROLE_ADMIN',
                    'ROLE_MODERATOR' => 'ROLE_MODERATOR', ],

                'choice_attr' => [
                    'ROLE_USER' => ['data-mandatory' => 'true',
                        'selected' => 'selected'],
                ],
                'attr' => [],
            ])
            ->add('userDeactivate', UserDeactivateForm::class, [
                'label' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
