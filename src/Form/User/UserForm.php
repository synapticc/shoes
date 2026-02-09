<?php

// src/Form/User/UserForm.php

namespace App\Form\User;

use App\Entity\User\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', ChoiceType::class, [
                'label' => 'Title',
                'required' => true,
                'choices' => [
                    'Mr' => 'Mr',
                    'Mrs' => 'Mrs',
                    'Miss' => 'Miss',
                ],
            ])
            ->add('firstName')
            ->add('lastName')
            ->add('middleName')
            ->add('birthday', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'required' => false,
                'format' => 'dd/MM/yyyy',
                // 'format' => 'YYYY-MM-DD HH:mm',
                // 'date_format'=>"dd/MM/yyyy hh:mm",
            ])
            ->add('userPhone', UserPhoneForm::class, [
                'label' => false,
                'required' => true,
            ])
            ->add('userAddress', UserAddressForm::class, [
                'label' => false,
                'required' => true,
            ])
            ->add('userEmail', UserEmailForm::class, [
                'label' => false,
                'required' => true,
            ])
            ->add('image', FileType::class, [
                'mapped' => false,
                'required' => false,
            ])

              // 'ROLE_USER' is added to all users (admin members and customers)
            // ->add('roles', ChoiceType::class, [
            //     'multiple' => true,
            //     'required' => false,
            //     'label' => false,
            //
            //     'choices'  => [
            //         'ROLE_USER' => 'ROLE_USER',
            //         'ROLE_ADMIN' => 'ROLE_ADMIN',
            //         'ROLE_MODERATOR' => 'ROLE_MODERATOR',],
            //
            //     'choice_attr' => [
            //         'ROLE_USER' => ['data-mandatory' => 'true',
            //                         'selected' => 'selected'],
            //     ],
            //     'attr' => ['class' => 'form-control',]
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
