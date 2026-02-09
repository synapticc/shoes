<?php

// src/Form/Product/ProductData/ProductColorTextureForm.php

namespace App\Form\Product\ProductData;

use App\Controller\_Utils\Attributes;
use App\Entity\Product\ProductColor\ProductColorTexture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductColorTextureForm extends AbstractType
{
    use Attributes;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
          ->add('textures', ChoiceType::class, [
              'multiple' => true,
              'required' => false,
              'choices' => $this->getTextureSet(),
              'attr' => ['class' => '', 'data-textures-select' => ''],
          ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductColorTexture::class,
        ]);
    }
}
