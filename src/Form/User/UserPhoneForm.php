<?php

// src/Form/User/UserPhoneForm.php

namespace App\Form\User;

use App\Entity\User\UserPhone;
use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserPhoneForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mobile', PhoneNumberType::class, [
                'required' => false,
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                'country_display_type' => 'display_country_short',
                'preferred_country_choices' => ['MU', // Mauritius
                    'JP', // Japan
                    'GB', // United Kingdom
                    'ZA', // South Africa
                    'US', // United States
                ],
                'default_region' => 'MU',
                'format' => PhoneNumberFormat::NATIONAL,
            ])
            ->add('landline', PhoneNumberType::class, [
                'required' => false,
                'default_region' => 'MU',
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                'format' => PhoneNumberFormat::NATIONAL,
                'country_display_type' => 'display_country_short',
                'preferred_country_choices' => ['MU', // Mauritius
                    'JP', // Japan
                    'GB', // United Kingdom
                    'ZA', // South Africa
                    'US', // United States
                ],
            ])
            ->add('fax')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserPhone::class,
        ]);
    }
}
