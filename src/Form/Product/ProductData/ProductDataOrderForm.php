<?php

// src/Form/Product/ProductData/ProductDataOrderForm.php

namespace App\Form\Product\ProductData;

use App\Entity\Product\ProductData\ProductDataOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductDataOrderForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('qtyOnOrder', IntegerType::class, [
                'label' => 'Quantity (on order)',
                'required' => true,
            ])
            ->add('reorderLevel', IntegerType::class, [
                'label' => 'Reorder level',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductDataOrder::class,
        ]);
    }
}
