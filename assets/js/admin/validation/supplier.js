// assets/js/admin/validation/supplier.js

import {ZephyrFormValidator as Validator } from '../../../plugins/admin/zephyr/ZephyrFormValidator.js';
import smoothScroll from 'smoothscroll';

if (document.querySelector('form[name="supplier_form"]') !== null)
{
  // Get the form element
  let form = document.querySelector('form[name="supplier_form"]');
  // Validate on form submission
  form.addEventListener('submit', (e) =>
  {
    const options =
    {
      fields: {
        'supplier_form[name]': {
          required: {
            value: true,
            message: "The name is required.", }
        },
        'supplier_form[email]': {
          required: {
            value: true,
            message: "The email is required.", },
        },
        'supplier_form[phone][country]': {
          required: {
            value: true,
            message: "The phone code is required.", },
        },
        'supplier_form[phone][number]': {
          required: {
            value: true,
            message: "The phone number is required.", },
        },
        'supplier_form[street]': {
          required: {
            value: true,
            message: "The street is required.", },
        },
        'supplier_form[country_code]': {
          required: {
            value: true,
            message: "The country is required.", },
        },
      },
      errorClass: "validation-error",
      validationClasses: {
        isInvalid: {
          input: "is-invalid",
          error: "invalid-feedback"
        }
      }
    };

    // Initialize the validator
    let validator = new Validator(form, options);

    if (!validator.validate())
    {
      /* After preventing the form submission, scroll to the first error message.  */
      let scrollTarget = document.querySelector('.invalid-feedback').closest('div');
      smoothScroll(scrollTarget, 300);

      e.preventDefault();
    };
  });
}
