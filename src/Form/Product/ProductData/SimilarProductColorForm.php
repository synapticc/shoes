<?php

// src/Form/Product/ProductData/SimilarProductColorForm.php

namespace App\Form\Product\ProductData;

use App\Entity\Product\ProductColor\SimilarProductColor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SimilarProductColorForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('excludeProductColors', CollectionType::class, [
                'label' => 'Exclude product colors: ',
                'required' => false,
                'entry_type' => ExcludeProductColorForm::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'prototype' => true,
                'entry_options' => [
                    'attr' => ['class' => 'row', 'data-product-color' => ''],
                ],
                'attr' => ['class' => 'form-control mb-3', 'data-exclude-colors' => ''],
            ])
            ->add('excludeColor', ExcludeColorForm::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('sort', null, [
                'mapped' => true,
                'compound' => true,
                'allow_extra_fields' => true,
                'invalid_message' => 'Color sort should not contain extra fields.',
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SimilarProductColor::class,
        ]);
    }
}
