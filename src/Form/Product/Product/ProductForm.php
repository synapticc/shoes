<?php

// src/Form/Product/Product/ProductForm.php

namespace App\Form\Product\Product;

use App\Controller\_Utils\Attributes;
use App\Entity\Product\Product\Product;
use App\Form\Product\ProductData\ProductColorForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductForm extends AbstractType
{
    use Attributes;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
          ->add('name', TextType::class, [
              'required' => true,
          ])
          ->add('description', TextareaType::class, [
              'required' => false,
              'attr' => ['maxlength' => 1200],
          ])
          ->add('features', CollectionType::class, [
              'required' => false,
              'entry_type' => TextType::class,
              'allow_add' => true,
              'allow_delete' => true,
              'delete_empty' => true,
              'prototype' => true,
              'entry_options' => [
                  'attr' => ['class' => 'form-control'],
              ],
          ])
          ->add('brand', ChoiceType::class, [
              'placeholder' => 'Choose brand',
              'required' => true,
              'choices' => $this->brandSet(true),
              'attr' => ['data-label' => 'brand'],
          ])
          ->add('category', ChoiceType::class, [
              'placeholder' => 'Choose category',
              'required' => true,
              'choices' => $this->getCategorySet(true),
              'attr' => ['data-label' => 'category'],
          ])
          ->add('occasion', ChoiceType::class, [
              'multiple' => true,
              'placeholder' => 'Choose occasion',
              'required' => true,
              'choices' => $this->getOccasionSet(true),
              'attr' => ['data-label' => 'occasion'],
          ])
          ->add('type', ChoiceType::class, [
              'placeholder' => 'Choose type',
              'required' => true,
              'choices' => $this->getTypeSet(true),
              'attr' => ['data-label' => 'type'],
          ])
          ->add('qtyPack', ProductQtyPackForm::class, [
              'label' => false,
              'required' => false,
          ])
          ->add('displayed', CheckboxType::class, [
              'required' => false, ])
          ->add('displayDate', DateTimeType::class, [
              'widget' => 'single_text',
              'html5' => false,
              'required' => true,
              'format' => 'yyyy-MM-dd HH:mm',
          ])
          ->add('video', ProductVideoForm::class, [
              'label' => false,
              'required' => false,
          ])
          ->add('discontinued', ProductDiscontinuedForm::class, [
              'label' => false,
              'required' => false,
          ])
          ->add('pricing', ProductPricingForm::class, [
              'label' => false,
              'required' => false,
          ])
          ->add('discount', ProductDiscountForm::class, [
              'label' => false,
              'required' => false,
          ])
          // ->add('colors',CollectionType::class,[
          //     'label' => false,
          //     'mapped' => false,
          //     'required' => false,])
          ->add('colors', CollectionType::class, [
              'label' => false,
              'mapped' => false,
              'required' => false,
              'entry_type' => ProductColorForm::class,
              'allow_add' => true,
              'allow_delete' => true,
              'delete_empty' => true,
              'prototype' => true,
              'entry_options' => [
                  'attr' => [],
              ],
          ])
          ->add('similarProduct', SimilarProductForm::class, [
              'required' => false,
          ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'csrf_protection' => true,
            // the name of the hidden HTML field that stores the token
            'csrf_field_name' => '_token',
        ]);
    }
}
