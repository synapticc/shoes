<?php

// src/Form/Billing/Transfer/CartItemTransferForm.php

namespace App\Form\Billing\Transfer;

use App\Entity\NoMap\Transfer\Billing\OrderItemTransfer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CartItemTransferForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity')
            ->add('remove', SubmitType::class, [
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderItemTransfer::class,
            // 'data_class' => null,
        ]);
    }
}
