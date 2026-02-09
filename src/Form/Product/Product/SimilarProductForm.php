<?php

// src/Form/Product/Product/SimilarProductForm.php

namespace App\Form\Product\Product;

use App\Controller\_Utils\Attributes;
use App\Entity\Product\Product\SimilarProduct;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SimilarProductForm extends AbstractType
{
    use Attributes;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $brands = [
            'placeholder' => 'Choose brand',
            'choices' => $this->brandSet(),
            'multiple' => true,
            'attr' => ['data-label' => 'brands'],
            'required' => true];
        $occasions = [
            'placeholder' => 'Choose occasion',
            'choices' => $this->getOccasionSet(),
            'attr' => ['data-label' => 'occasions'],
            'multiple' => true,
            'required' => true];
        $types = [
            'placeholder' => 'Choose type',
            'choices' => $this->getTypeSet(),
            'attr' => ['data-label' => 'types'],
            'multiple' => true,
            'required' => true];
        $colors = [
            'placeholder' => 'Choose color',
            'label' => false,
            'required' => true,
            'choices' => $this->getColorSet(),
            'multiple' => true,
            'required' => true];
        $fabrics = [
            'placeholder' => 'Choose fabric',
            'multiple' => true,
            'required' => true,
            'choices' => $this->getFabricSet()];
        $textures = [
            'placeholder' => 'Choose textures',
            'multiple' => true,
            'required' => false,
            'choices' => $this->getTextureSet()];
        $tags = [
            'placeholder' => 'Choose tags',
            'multiple' => true,
            'required' => false,
            'choices' => $this->getTagSet()];
        $sizes = [
            'placeholder' => 'Choose size',
            'multiple' => true,
            'required' => true,
            'choices' => $this->sizeFull()];
        $minPrice = [
            'label' => 'Minimum price: ',
            'required' => true];
        $maxPrice = [
            'label' => 'Minimum price: ',
            'required' => true];
        // $otherProducts = [
        //   'required' => false,
        //   'entry_type' => OtherProductForm::class,
        //   'allow_add' => true,
        //   'allow_delete' => true,
        //   'delete_empty' => true,
        //   'prototype' => true,
        //   'entry_options' =>[
        //     'attr' => ['class' => '','data-product' => '']],
        //   'attr' => ['class' => 'form-control mb-3',
        //              'data-products' => '']];
        $qtyRequired = [
            'mapped' => false,
            'required' => true,
            'attr' => ['min' => 0, 'max' => 30]];
        $qtyOptional = [
            'mapped' => false,
            'required' => false,
            'attr' => ['min' => 0, 'max' => 30]];

        $builder
            ->add('brands', ChoiceType::class, $brands)
            ->add('occasions', ChoiceType::class, $occasions)
            ->add('types', ChoiceType::class, $types)
            ->add('colors', ChoiceType::class, $colors)
            ->add('fabrics', ChoiceType::class, $fabrics)
            ->add('textures', ChoiceType::class, $textures)
            ->add('tags', ChoiceType::class, $tags)
            ->add('sizes', ChoiceType::class, $sizes)
            ->add('minPrice', NumberType::class, $minPrice)
            ->add('maxPrice', NumberType::class, $maxPrice)

            ->add('qtyBrand', IntegerType::class, $qtyRequired)
            ->add('qtyOccasion', IntegerType::class, $qtyRequired)
            ->add('qtyType', IntegerType::class, $qtyRequired)
            ->add('qtyColor', IntegerType::class, $qtyRequired)
            ->add('qtyFabric', IntegerType::class, $qtyRequired)
            ->add('qtyTexture', IntegerType::class, $qtyOptional)
            ->add('qtyTags', IntegerType::class, $qtyOptional)
            ->add('qtySize', IntegerType::class, $qtyOptional)

            ->add('brandType', IntegerType::class, $qtyRequired)
            ->add('brandColor', IntegerType::class, $qtyRequired)
            ->add('brandColorType', IntegerType::class, $qtyOptional)
            ->add('brandOccasion', IntegerType::class, $qtyOptional)
            ->add('brandOccasionType', IntegerType::class, $qtyOptional)
            ->add('brandOccasionColor', IntegerType::class, $qtyOptional)

            ->add('colorType', IntegerType::class, $qtyOptional)
            ->add('colorOccasion', IntegerType::class, $qtyOptional)
            ->add('colorFabric', IntegerType::class, $qtyOptional)
            ->add('colorTexture', IntegerType::class, $qtyOptional)

            ->add('fabricType', IntegerType::class, $qtyOptional)
            ->add('fabricTexture', IntegerType::class, $qtyOptional)

            ->add('bestSellerProduct', IntegerType::class, $qtyOptional)
            ->add('bestReviewProduct', IntegerType::class, $qtyOptional)

            ->add('description', IntegerType::class, $qtyOptional)
            ->add('features', IntegerType::class, $qtyOptional)
            ->add('sort')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SimilarProduct::class,
        ]);
    }
}
