<?php

// src/Form/Billing/BillingDeliveryForm.php

namespace App\Form\Billing;

use App\Entity\Billing\BillingDelivery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BillingDeliveryForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
          ->add('deliveryNotes', TextareaType::class, [
              'required' => false,
              'attr' => ['maxlength' => 500],
          ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BillingDelivery::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
        ]);
    }
}
