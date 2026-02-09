<?php

// src/Form/Billing/BillingForm.php

namespace App\Form\Billing;

use App\Entity\Billing\Billing;
use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BillingForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mobile', PhoneNumberType::class, [
                'required' => true,
                'default_region' => 'MU',
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                'format' => PhoneNumberFormat::NATIONAL,
                'country_display_type' => 'display_country_full',
                'preferred_country_choices' => ['MU', // Mauritius
                    'JP', // Japan
                    'GB', // United Kingdom
                    'ZA', // South Africa
                    'US', // United States
                ],
            ])
            ->add('landline', PhoneNumberType::class, [
                'required' => false,
                'default_region' => 'MU',
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                'format' => PhoneNumberFormat::NATIONAL,
                'country_display_type' => 'display_country_full',
                'preferred_country_choices' => ['MU', // Mauritius
                    'JP', // Japan
                    'GB', // United Kingdom
                    'ZA', // South Africa
                    'US', // United States
                ],
            ])
            ->add('street', TextType::class, [
                'label' => 'Street*',
                'required' => true,
            ])
            ->add('city', ChoiceType::class, [
                'attr' => ['data-city' => ''],
                'label' => 'City*',
                'required' => true,
            ])
            ->add('zip', IntegerType::class, [
                'label' => 'ZIP*',
                'required' => true,
            ])
            ->add('country', CountryType::class, [
                'attr' => ['data-country' => ''],
                'label' => 'Country*',
                'required' => true,
                'preferred_choices' => ['MU', // Mauritius
                    'SC', // Seychelles
                    'ZA', // South Africa
                    'RE', // Reunion
                ],
            ])
            ->add('cardNumber', TextType::class, [
                'required' => true,
            ])
            ->add('expiryDate', DateType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'required' => true,
                // 'format' => 'dd/MM/yyyy',
                'format' => 'yyyy-MM-dd',
            ])
            ->add('cvc', TextType::class, [
                'required' => true,
            ])
            ->add('cardHolder', TextType::class, [
                'required' => true,
            ])
            ->add('billingDelivery', BillingDeliveryForm::class, [
                'label' => false,
                'required' => false,
            ])
            /* The following event is used to inject the selected city
              as an option to the 'city' select. This allows the Symfony Form
              Validator to recognize and validate the new value. The event occurs
              before the form submission.
            */
            ->addEventListener(
                FormEvents::PRE_SUBMIT,
                function (PreSubmitEvent $event): void {
                    // fetch submitted value
                    $data = $event->getData()['city'];
                    $form = $event->getForm();

                    // retrieve original select field options, so we won't need to repeat them
                    $opts = $form->get('city')->getConfig()->getOptions();

                    // here we're adding our fetched submitted value to the list of select field options
                    $opts['choices'][$data] = $data;

                    $form->remove('city');

                    // add reconfigured (=with changed options) field
                    $form->add('city', ChoiceType::class, [
                        'choices' => $opts['choices'],
                    ]);
                }
            )->getForm()
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Billing::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
        ]);
    }
}
