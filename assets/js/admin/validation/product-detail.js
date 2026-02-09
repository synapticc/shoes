// assets/js/admin/validation/product-detail.js

import {ZephyrFormValidator as Validator } from '../../../plugins/admin/zephyr/ZephyrFormValidator.js';
import smoothScroll from 'smoothscroll';

if (document.querySelector('form[name="product_data_form"]') !== null)
{
  // Get the form element
  let form = document.querySelector('form[name="product_data_form"]');
  // Validate on form submission
  form.addEventListener('submit', (e) =>
  {
    const options =
    {
      fields: {
        'product_data_form[product]': {
          required: {
            value: true,
            message: "Product is required.", }
        },
        'product_data_form[supplier][supplier]': {
          required: {
            value: true,
            message: "Supplier is required.", }
        },
        'product_data_form[color]': {
          required: {
            value: true,
            message: "Color is required.", },
        },
        'product_data_form[size]': {
          required: {
            value: true,
            message: "Size is required.", },
        },
        'product_data_form[costPrice]': {
          required: {
            value: true,
            message: "Cost price is required.", },
        },
        'product_data_form[sellingPrice]': {
          required: {
            value: true,
            message: "Selling price is required.", },
        },
        'product_data_form[qtyInStock]': {
          required: {
            value: true,
            message: "Quantity (in stock)  is required.", },
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
      /*  Expand only the sections which contain an error message */
      let collapseButtons = form.querySelectorAll('[data-toggle]');
      collapseButtons.forEach((collapseButton, i) =>
      {
        let errorTarget =  document.querySelector(collapseButton.dataset.target).querySelector('.invalid-feedback');

        if (errorTarget != null)
          if (collapseButton.ariaExpanded !== 'true')
            collapseButton.click();
      });


      /* Smooth scroll to the first error message  */
      let scrollTarget = document.querySelector('.invalid-feedback').closest('div');
      smoothScroll(scrollTarget, 300);

      /* Prevent form submission*/
      e.preventDefault();
    };
  });
}
