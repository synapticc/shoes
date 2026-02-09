<?php

// src/Form/Supplier/SupplierDataForm.php

namespace App\Form\Supplier;

use App\Entity\Supplier\Supplier;
use App\Entity\Supplier\SupplierData;
use App\Repository\Supplier\SupplierRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupplierDataForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('supplier', EntityType::class, [
                'class' => Supplier::class,
                'placeholder' => 'Choose supplier',
                'required' => false,
                'query_builder' => fn (SupplierRepository $er) => $er->createQueryBuilder('s')
                    ->orderBy('s.id', 'ASC'),
                'choice_label' => 'name',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SupplierData::class,
        ]);
    }
}
