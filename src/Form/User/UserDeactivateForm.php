<?php

// src/Form/User/UserDeactivateForm.php

namespace App\Form\User;

use App\Entity\User\UserDeactivate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserDeactivateForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
          ->add('deactivate', CheckboxType::class, [
              'required' => false, ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserDeactivate::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
