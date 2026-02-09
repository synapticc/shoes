<?php

// src/Form/Review/ReviewRecommendForm.php

namespace App\Form\Review;

use App\Entity\Review\ReviewRecommend;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewRecommendForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('recommend', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'Recommend' => true,
                    'Would not recommend' => false,
                ],
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReviewRecommend::class,
        ]);
    }
}
