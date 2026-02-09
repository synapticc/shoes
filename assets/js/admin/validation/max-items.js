// assets/js/admin/validation/max-items.js

import {ZephyrFormValidator as Validator } from '../../../plugins/admin/zephyr/ZephyrFormValidator.js';

if (document.querySelector('form[name="max_items_form"]') !== null)
{
  // Get the form element
  let form = document.querySelector('form[name="max_items_form"]');

  // Initialize the validator
  let validator = new Validator(form, {
    fields: {
      'max_items_form[listing]': {
        required: {
          value: true,
          message: "required.", },
        range: {
          min: 10,
          max: 50,
          message: "10 to 50 only."
        }
      },
      'max_items_form[reviews]': {
        required: {
          value: true,
          message: "required.", },
        range: {
          min: 5,
          max: 20,
          message: "5 to 20 only."
        }
      },
      'max_items_form[recent]': {
        required: {
          value: true,
          message: "required.", },
        range: {
          min: 5,
          max: 25,
          message: "5 to 25 only."
        }
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
  form.addEventListener('submit', (e) => {
    if (!validator.validate()) e.preventDefault();
  });
}
