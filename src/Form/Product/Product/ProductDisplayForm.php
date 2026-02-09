<?php

// src/Form/Product/Product/ProductDisplayForm.php

namespace App\Form\Product\Product;

use App\Entity\Product\Product\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductDisplayForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
          ->add('displayed', CheckboxType::class, [
              'required' => false, ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'method' => 'GET',
            'csrf_protection' => false,
            // the name of the hidden HTML field that stores the token
            // 'csrf_field_name' => 'token',
        ]);
    }
}
