<?php

// src/Form/Product/ProductData/ProductColorTagsForm.php

namespace App\Form\Product\ProductData;

use App\Controller\_Utils\Attributes;
use App\Entity\Product\ProductColor\ProductColorTags;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductColorTagsForm extends AbstractType
{
    use Attributes;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
              ->add('tags', ChoiceType::class, [
                  'multiple' => true,
                  // 'mapped' => false,
                  'required' => false,
                  'choices' => $this->getTagSet(),
                  'attr' => ['class' => '', 'data-tags-select' => ''],
              ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductColorTags::class,
        ]);
    }
}
