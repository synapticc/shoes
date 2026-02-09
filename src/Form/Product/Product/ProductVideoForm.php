<?php

// src/Form/Product/Product/ProductVideoForm.php

namespace App\Form\Product\Product;

use App\Entity\Product\Product\ProductVideo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductVideoForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('videoUrl', TextType::class, [
                'label' => 'Video URL: ',
                // 'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductVideo::class,
        ]);
    }
}
