<?php

// src/Form/Product/Product/ProductDelete/DeleteImageForm.php

namespace App\Form\Product\Product\ProductDelete;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeleteImageForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
          ->add('delete-image1', CheckboxType::class, [
              'required' => false,
              'mapped' => false, ])
          ->add('delete-image2', CheckboxType::class, [
              'required' => false,
              'mapped' => false, ])
          ->add('delete-image3', CheckboxType::class, [
              'required' => false,
              'mapped' => false, ])
          ->add('delete-image4', CheckboxType::class, [
              'required' => false,
              'mapped' => false, ])
          ->add('delete-image5', CheckboxType::class, [
              'required' => false,
              'mapped' => false, ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'method' => 'GET',
            'csrf_protection' => false,
            // the name of the hidden HTML field that stores the token
            // 'csrf_field_name' => 'token',
        ]);
    }
}
