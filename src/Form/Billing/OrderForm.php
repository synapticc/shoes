<?php

// src/Form/Billing/OrderForm.php

namespace App\Form\Billing;

use App\Entity\Billing\Order;
use App\Form\Events\ClearCartListener;
use App\Form\Events\RemoveCartItemListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('items', CollectionType::class, [
                'entry_type' => CartItemForm::class,

                /* Other options for form embedding */
                // 'data'          => $options['pagination'],
                // 'allow_add'     => true,
                // 'allow_delete'  => true,
                // 'prototype'     => true,
                // 'label'         => false,
                // 'by_reference'  => true,
                // 'mapped' => true
            ])
            ->add('activeStatus', CheckboxType::class, [
                'required' => false,
                'attr' => [
                    'data-status' => '', ],
            ])
            ->add('save', SubmitType::class)
            ->add(
                'clear',
                SubmitType::class,
                [
                    'label' => 'Clear',
                ]
            )
            ->add('delete', SubmitType::class);

        $builder->addEventSubscriber(new RemoveCartItemListener());
        $builder->addEventSubscriber(new ClearCartListener());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
            // 'data_class' => null
        ]);
    }
}
