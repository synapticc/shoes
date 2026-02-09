<?php

// src/Form/Product/Product/ProductQtyPackForm.php

namespace App\Form\Product\Product;

use App\Entity\Product\Product\ProductQtyPack;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductQtyPackForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('qtyPack', ChoiceType::class, [
                'placeholder' => 'Choose pack',
                'choices' => [2 => 2, 3 => 3, 4 => 4],
                'attr' => ['data-label' => 'pack'],
            ])
            // ->add('qtyPack', IntegerType::class, [
            //   'required' => true,
            //   'attr' => ['min' => 1,
            //              'max' => 8]
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductQtyPack::class,
        ]);
    }
}
