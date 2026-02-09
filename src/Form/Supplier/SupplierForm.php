<?php

// src/Form/Supplier/SupplierForm.php

namespace App\Form\Supplier;

use App\Entity\Supplier\Supplier;
use App\Form\Events\AddressListener;
use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupplierForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
            ])
            ->add('details', TextareaType::class, [
                'required' => false,
            ])
            ->add('email', TextType::class, [
                'required' => false,
            ])
            ->add(
                'phone',
                PhoneNumberType::class,
                [
                    'required' => false,
                    'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                    'format' => PhoneNumberFormat::NATIONAL,
                    'country_display_type' => 'display_country_full',
                    'preferred_country_choices' => ['MU', // Mauritius
                        'SC', // Seychelles
                        'ZA', // South Africa
                        'RE', // Reunion
                    ],
                ]
            )
            ->add('street', TextType::class, [
                'required' => false,
            ])
            ->add('city', ChoiceType::class, [
                'required' => true,
            ])
            ->add('countryCode', CountryType::class, [
                'required' => true,
                'mapped' => true,
                'preferred_choices' => ['MU', // Mauritius
                    'SC', // Seychelles
                    'ZA', // South Africa
                    'RE', // Reunion
                ],
                'duplicate_preferred_choices' => true,
            ])
            ->addEventSubscriber(new AddressListener())
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Supplier::class,
        ]);
    }
}
