<?php

// src/Form/Review/ReviewForm.php

namespace App\Form\Review;

use App\Entity\Review\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Dropzone\Form\DropzoneType;

class ReviewForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reviewDelivery', ReviewDeliveryForm::class, [
                'required' => false,
            ])
            ->add('reviewLike', ReviewLikeForm::class, [
                'required' => false,
            ])
            ->add('reviewRecommend', ReviewRecommendForm::class, [
                'required' => false,
            ])
            ->add('headline', TextType::class, [
                'required' => true,
            ])
            ->add('comment', TextareaType::class)
            ->add('fit', RangeType::class, [
                'required' => true,
                'attr' => [
                    'min' => 1,
                    'max' => 5,
                ],
            ])
            ->add('comfort', RangeType::class, [
                'required' => true,
                'attr' => [
                    'min' => 1,
                    'max' => 5,
                ],
            ])
            ->add('width', RangeType::class, [
                'required' => true,
                'attr' => [
                    'min' => 1,
                    'max' => 5,
                ],
            ])
            ->add('rating', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                ],
            ])
            ->add('image', DropzoneType::class, [
                'attr' => [
                    'data-controller' => 'droparea',
                    'data-action' => 'dragleave->droparea#onDragLeave:prevent:stop',
                    // "dragleave->droparea#onDragLeave:prevent:stop",
                    // dragover->droparea#dragOver
                    'placeholder' => 'Image 1: Choose Or Drop'],
                'mapped' => false,
                'required' => false,
            ])
            ->add('image2', DropzoneType::class, [
                'attr' => [
                    'data-controller' => 'droparea',
                    'data-action' => 'dragleave->droparea#onDragLeave:prevent:stop',
                    'placeholder' => 'Image 2: Choose Or Drop'],
                'mapped' => false,
                'required' => false,
            ])
            ->add('image3', DropzoneType::class, [
                'attr' => [
                    'data-controller' => 'droparea',
                    'data-action' => 'dragleave->droparea#onDragLeave:prevent:stop',
                    'placeholder' => 'Image 3: Choose Or Drop'],
                'mapped' => false,
                'required' => false,
            ])
            ->add('image4', DropzoneType::class, [
                'attr' => [
                    'data-controller' => 'droparea',
                    'data-action' => 'dragleave->droparea#onDragLeave:prevent:stop',
                    'placeholder' => 'Image 4: Choose Or Drop'],
                'mapped' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}
