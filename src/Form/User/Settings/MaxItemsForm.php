<?php

// src/Form/User/Settings/MaxItemsForm.php

namespace App\Form\User\Settings;

use App\Entity\User\Settings\MaxItems;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MaxItemsForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $listing = [
            'required' => true,
            'attr' => ['min' => 10, 'max' => 50]];
        $reviews = [
            'required' => true,
            'attr' => ['min' => 3, 'max' => 10]];
        $recent = [
            'required' => true,
            'attr' => ['min' => 3, 'max' => 20]];

        $builder->add('listing', IntegerType::class, $listing);
        $builder->add('reviews', IntegerType::class, $reviews);
        $builder->add('recent', IntegerType::class, $recent);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MaxItems::class,
        ]);
    }
}
