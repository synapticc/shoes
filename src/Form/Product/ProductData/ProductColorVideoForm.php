<?php

// src/Form/Product/ProductData/ProductColorVideoForm.php

namespace App\Form\Product\ProductData;

use App\Entity\Product\ProductColor\ProductColorVideo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductColorVideoForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('videoUrl', TextType::class, [
                'label' => 'Video URL: ',
                // 'required' => false,
                'attr' => ['class' => 'form-control d-inline-block w-50'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductColorVideo::class,
        ]);
    }
}
