<?php

// src/Form/User/Settings/AdminUserInfoForm.php

namespace App\Form\User\Settings;

use App\Entity\User\User;
use App\Form\User\UserAddressForm;
use App\Form\User\UserEmailForm;
use App\Form\User\UserPhoneForm;
use App\Form\User\UserPositionForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminUserInfoForm extends AbstractType
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
          ->add('userPosition', UserPositionForm::class, [
              'required' => false,
          ])
          ->add('birthday', BirthdayType::class, [
              'widget' => 'single_text',
              // 'format' => 'yyyy-MM-dd',
              // 'format' => 'dd-MM-yyyy',
              // 'format' => 'yyyy-MM-dd',
          ])
          ->add('userPhone', UserPhoneForm::class, [
              'label' => false,
              'required' => true,
          ])
          ->add('userAddress', UserAddressForm::class, [
              'label' => false,
              'required' => false,
          ])
          ->add('userEmail', UserEmailForm::class, [
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
