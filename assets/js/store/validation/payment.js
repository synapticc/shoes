// assets/js/store/validation/payment.js

import {ZephyrFormValidator as Validator } from '../../../plugins/admin/zephyr/ZephyrFormValidator.js';
import DatPayment from '../../../plugins/store/DatPayment/js/DatPayment.js';
import smoothScroll from 'smoothscroll';
import * as Card from "card";
import empty from 'is-blank';


if (document.querySelector('form[name="billing_form"]') !== null)
{
    let paymentOptions =
    {
      form_selector: '#billing-form',
      card_container_selector: '.dpf-card-placeholder',

      number_selector: '.dpf-input[data-type="number"]',
      date_selector: '.dpf-input[data-type="expiry"]',
      cvc_selector: '.dpf-input[data-type="cvc"]',
      name_selector: '.dpf-input[data-type="name"]',

      submit_button_selector: '.dpf-submit',

      placeholders: {
          number: '•••• •••• •••• ••••',
          expiry: '••/••',
          cvc: '•••',
          name: 'EMILY'
      },

      validators:
      {
          number: (number) => Stripe.card.validateCardNumber(number),
          expiry: (expiryTxt) =>
          {
            let expiry = expiryTxt.split(' / ');
            return Stripe.card.validateExpiry(expiry[0]||0,expiry[1]||0);
          },
          cvc: (cvc) => Stripe.card.validateCVC(cvc),
          name: (value) => value.length > 0
      }
    };

    let payment = new DatPayment(paymentOptions);

    let expiryTarget = document.getElementById('billing_form_expiryDate'),
        expiryInput = document.querySelector('[data-type="expiry"]');

    expiryInput.addEventListener(
        'input', (e) =>
        {
          let expiryTxt =  expiryInput.value.split(' / '),
              expiryDate;

          expiryDate = `${expiryTxt[1]}-${expiryTxt[0]}-01`;
          expiryTarget.value = expiryDate;
          expiryTarget.setAttribute('value', expiryDate);
        });

    // Get the form element
    let billingForm = document.querySelector('form[name="billing_form"]');

    // Initialize the validator
    let validator = new Validator(billingForm, {
      fields: {
        'billing_form[mobile][number]': {
          required: {
            value: true,
            message: "Mobile number is required.", }
        },
        'billing_form[street]': {
          required: {
            value: true,
            message: "Street is required." }
        },
        'billing_form[city]': {
          required: {
            value: true,
            message: "City is required."}
        },
        'billing_form[zip]': {
          required: {
            value: true,
            message: "ZIP is required."}
        },
        'billing_form[country]': {
          required: {
            value: true,
            message: "Country is required."}
        },
        'billing_form[cardNumber]': {
          required: {
            value: true,
            message: "Card number is required."}
        },
        'billing_form[expiryDate]': {
          required: {
            value: true,
            message: "Expiry date is required."}
        },
        'billing_form[cvc]': {
          required: {
            value: true,
            message: "Security code is required."}
        },
        'billing_form[cardHolder]': {
          required: {
            value: true,
            message: "Card holder is required."}
        },
      },
      errorClass: "validation-error",
      validationClasses: {
        isInvalid: {
          input: "is-invalid",
          error: "invalid-feedback"
        }
      }
    });

    // Validate on form submission
    billingForm.addEventListener('submit', (e) =>
      {

      if (validator.validate() === false)
      {
        e.preventDefault();
        // e.stopPropagation();

        /* Smooth scroll to the first error message  */
        let scrollTarget = document.querySelector('.invalid-feedback')
                          .closest('[error-parent]');
        if (!empty(scrollTarget))
          smoothScroll(scrollTarget, 300);

      }
      else if (validator.validate() === true &&
               payment.validateForm() === 1)
      {
        billingForm.submit();
      }
      });

    payment.form.addEventListener(
        'payment_form:submit',(e) =>
        {
          // Replace all white spaces in card number
          let cardNumber = document.getElementById('billing_form_cardNumber'),
          clean = cardNumber.value.replace(/\s+/g,"");
          cardNumber.value = clean;
          cardNumber.setAttribute('value', clean);

          if (validator.validate() === true)
          {
            payment.unlockForm();
            payment.submit();
          }
        });
}
