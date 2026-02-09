// assets/js/store/validation/review.js

import empty from 'is-blank';
import smoothScroll from 'smoothscroll';
import {ZephyrFormValidator as Validator } from '../../../plugins/admin/zephyr/ZephyrFormValidator.js';

if (document.querySelector('form[name="review_form"]') !== null)
{
  // Get the form element
  let form = document.querySelector('form[name="review_form"]');

  // Initialize the validator
  let validator = new Validator(form, {
    fields: {
      'review_form[headline]': {
        required: {
          value: true,
          message: "Headline is required.", },
        min: {
          value: 5,
          message: "Headline must be at least 5 characters."
        },
        max: {
          value: 35,
          message: "Headline cannot exceed 35 characters."
        }
      },
      'review_form[comment]': {
        required: {
          value: true,
          message: "Comment is required." },
        min: {
          value: 25,
          message: "Comment must be at least 15 characters."
        },
        max: {
          value: 500,
          message: "Comment cannot exceed 500 characters."
        }
      },
      'review_form[rating]': {
        required: {
          value: true,
          message: "Rating is mandatory."}
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

  // Validate on form submission.
  form.addEventListener('submit', (e) => {
    if (validator.validate() === false)
    {
      e.preventDefault();

      /* Smooth scroll to the first error message  */
      let scrollTarget = document.querySelector('.invalid-feedback')
                        .closest('div');
      if (!empty(scrollTarget))
        smoothScroll(scrollTarget, 300);
    }
  });
}
