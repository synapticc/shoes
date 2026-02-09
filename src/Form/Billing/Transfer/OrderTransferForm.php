<?php

// src/Form/Billing/Transfer/OrderTransferForm.php

namespace App\Form\Billing\Transfer;

use App\Entity\NoMap\Transfer\Billing\OrderTransfer;
use App\Form\Events\ClearCartListener;
use App\Form\Events\RemoveCartItemListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderTransferForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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

        // $builder->addEventSubscriber(new RemoveCartItemListener());
        // $builder->addEventSubscriber(new ClearCartListener());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderTransfer::class,
            // 'data_class' => null
        ]);
    }
}
