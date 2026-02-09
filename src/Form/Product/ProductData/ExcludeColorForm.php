<?php

// src/Form/Product/ProductData/ExcludeColorForm.php

namespace App\Form\Product\ProductData;

use App\Controller\_Utils\Attributes;
use App\Entity\Product\ProductColor\ExcludeColor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExcludeColorForm extends AbstractType
{
    use Attributes;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('colors', ChoiceType::class, [
                'label' => false,
                'required' => true,
                'multiple' => true,
                'choices' => $this->getColorSet(true),
                'attr' => ['class' => 'form-control',
                    'data-exclude-color-select' => ''],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ExcludeColor::class,
        ]);
    }
}
