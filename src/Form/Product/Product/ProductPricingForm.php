<?php

// src/Form/Product/Product/ProductPricingForm.php

namespace App\Form\Product\Product;

use App\Entity\Product\Product\ProductPricing;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductPricingForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateDowngradePrice', DateTimeType::class, [
                'date_label' => 'Reduce price after: ',
                'required' => false,
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'MM-dd-yyyy',
            ])
            ->add('refundable', CheckboxType::class, [
                'required' => false, ])
            ->add('exchangeable', CheckboxType::class, [
                'required' => false, ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductPricing::class,
        ]);
    }
}
