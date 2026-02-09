<?php

// src/Form/Product/Product/ProductDiscontinuedForm.php

namespace App\Form\Product\Product;

use App\Entity\Product\Product\ProductDiscontinued;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductDiscontinuedForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('discontinued', CheckboxType::class, [
                'required' => false, ])

            ->add('dateDiscontinued', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'MM-dd-yyyy',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductDiscontinued::class,
        ]);
    }
}
