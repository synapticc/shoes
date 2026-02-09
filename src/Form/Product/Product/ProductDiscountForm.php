<?php

// src/Form/Product/Product/ProductDiscountForm.php

namespace App\Form\Product\Product;

use App\Entity\Product\Product\ProductDiscount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductDiscountForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('discount', PercentType::class, [
                'required' => true,
                'symbol' => false,
                'type' => 'integer',
                'attr' => [],
            ])
            ->add('startDate', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'required' => true,
                'format' => 'yyyy-MM-dd HH:mm',
            ])
            ->add('endDate', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'required' => true,
                'format' => 'yyyy-MM-dd HH:mm',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductDiscount::class,
        ]);
    }
}
