<?php

// src/Form/Product/ProductData/ProductColorForm.php

namespace App\Form\Product\ProductData;

use App\Controller\_Utils\Attributes;
use App\Entity\Product\ProductColor\ProductColor;
use App\Form\Product\Product\ProductDelete\DeleteImageForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductColorForm extends AbstractType
{
    use Attributes;

    /*
    // Rule of color selection
    shoe: shoe refers to sandals, slip-ons and all other footwears.

    The first color is the dominant one of the shoe.
    The second color is the less dominant one.
    The third color refers to patches or traces above
    or below the shoe.
    ex.
    Black / White => indicates that it is a black shoe with swatches
    of white.
    Black / White / Gold  => indicates that it is a black shoe with swatches
    of white and a few traces of gold (the laces for example).

    */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('colorId', IntegerType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
            ])
            ->add('color1', ChoiceType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
                'choices' => $this->getColorSet(),
                'attr' => ['class' => '', 'data-color-select' => ''],
            ])
            ->add('color2', ChoiceType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
                'choices' => $this->getColorSet(),
                'attr' => ['class' => '', 'data-color-select' => ''],
            ])
            ->add('color3', ChoiceType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
                'choices' => $this->getColorSet(),
                'attr' => ['class' => '', 'data-color-select' => ''],
            ])
            ->add('similarProductColor', SimilarProductColorForm::class, [
                'label' => false,
                'required' => false,
                'attr' => ['class' => 'form-control mb-3'],
            ])
            ->add('fabrics', ChoiceType::class, [
                'multiple' => true,
                'required' => false,
                'choices' => $this->getFabricSet(),
                'attr' => ['class' => '', 'data-fabrics-select' => ''],
            ])
            ->add('textures', ProductColorTextureForm::class, [
                'label' => false,
                'required' => false,
                'attr' => ['class' => 'mb-3'],
            ])
            ->add('video', ProductColorVideoForm::class, [
                'label' => false,
                'required' => false,
                'attr' => ['class' => 'mb-3'],
            ])
            ->add('tags', ProductColorTagsForm::class, [
                'label' => false,
                'required' => false,
                // 'mapped' => false,
                'attr' => ['class' => 'mb-3'],
            ])
            ->add('image1', FileType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('image2', FileType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('image3', FileType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('image4', FileType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('image5', FileType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('delete-image', DeleteImageForm::class, [
                'label' => false,
                'required' => false,
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductColor::class,
            'csrf_protection' => false,
        ]);
    }
}
