<?php

// src/Form/User/UserProfileBillingForm.php

namespace App\Form\User;

use App\Entity\User\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserProfileBillingForm extends AbstractType
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
            ->add('firstName', TextType::class, [
                'required' => true,
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
            ])
            ->add('middleName', TextType::class, [
                'required' => false,
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
