<?php

// src/Form/Review/ReviewLikeForm.php

namespace App\Form\Review;

use App\Entity\Review\ReviewLike;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewLikeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('like', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'Like' => true,
                    'Dislike' => false,
                ],
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReviewLike::class,
        ]);
    }
}
