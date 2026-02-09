<?php

// src/Form/User/UserAddressForm.php

namespace App\Form\User;

use App\Entity\User\UserAddress;
use App\Form\Events\AddressListener;
use Symfony\Component\Form\AbstractType;
// use PragmaRX\Countries\Package\Countries;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\OptionsResolver\OptionsResolver;
use ZipCodeValidator\Constraints\ZipCode;

class UserAddressForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // $countries = new Countries();
        // $mauritius = $countries->where('name.common', 'Mauritius')
        //           ->first()
        //           ->hydrateStates()
        //           ->states
        //           ->sortBy('name')
        //           ->pluck('name', 'name');

        // Use the above If we want to populate the Country <select> on loading.
        //  NOTE: The above package is taken from packagist(PragmaRX) and its cities
        //  list is different from that taken from npmjs(pangnote-cities).

        $builder
            ->add('street', TextType::class, [
                'required' => false,
            ])

            ->add('street2', TextType::class, [
                'required' => false,
            ])

            ->add('city', ChoiceType::class, [
                'attr' => ['data-city' => ''],
                // 'choices'  => $mauritius,
            ])

            ->add('zip', IntegerType::class, [
                'required' => false,
                'constraints' => [
                    new ZipCode([
                        'iso' => 'MU',
                    ])],
            ])

            ->add('country', CountryType::class, [
                'attr' => [],
                'preferred_choices' => ['MU', // Mauritius
                    'SC', // Seychelles
                    'ZA', // South Africa
                    'RE', // Reunion
                ],
            ])
            ->addEventSubscriber(new AddressListener())
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserAddress::class,
        ]);
    }
}
