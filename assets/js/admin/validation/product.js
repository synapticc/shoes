// assets/js/admin/validation/product.js

import {ZephyrFormValidator as Validator } from '../../../plugins/admin/zephyr/ZephyrFormValidator.js';
import smoothScroll from 'smoothscroll';
import empty from 'is-blank';

if (document.querySelector('form[name="product_form"]') !== null)
{
  /* Verify if at least one set of color/ images has been uploaded
     If not, display error message. */
  function validateImage()
  {
    let error = document.querySelector('.invalid-img-msg'),
    colorSection = document.getElementById('product_form_productColor');

    if(colorSection.childElementCount == 0)
    {
      error.style.display = 'block';
      return false;
    }
    else
    {
      error.style.display = 'none';
      return true;
    }
  }

  // Get the form element
  let form = document.querySelector('form[name="product_form"]');

  // Validate on form submission
  form.addEventListener('submit', (e) =>
  {

    let colorSection = document.getElementById('product_form_productColor'),
        tagSet = {}, options = {}
        ;
    /*
      The 'tagSet' JSON variable below aims to recreate the following format,
      which will then be added to the validator options.

      Example of JSON:
        'product_form[description]':
        {
          required:
          {
            value: true,
            message: "The description is required.", },
          min:
          {
            value: 25,
            message: "Description must be at least 25 characters long.", }
        },
    */
    if (colorSection.childElementCount > 0)
    {
      let requiredTags = colorSection.querySelectorAll('[data-required]');

      for (let i = 0; i < requiredTags.length; i++)
      {
        let requiredSet = {}, requiredContent = {};

        requiredContent.value = true;
        requiredContent.message = requiredTags[i].dataset.error;
        requiredSet.required = requiredContent;
        tagSet[requiredTags[i].name] = requiredSet;
      }
    }

    options =
    {
      fields: {
        'product_form[name]': {
          required: {
            value: true,
            message: "Name is required.", },
          min: {
            value: 3,
            message: "Name must be at least 3 characters long.", }
        },
        'product_form[description]': {
          required: {
            value: true,
            message: "Description is required.", },
          min: {
            value: 25,
            message: "Description must be at least 25 characters long.", }
        },
        'product_form[category]': {
          required: {
            value: true,
            message: "Category is required.", },
        },
        'product_form[type]': {
          required: {
            value: true,
            message: "Type is required.", },
        },
        'product_form[brand]': {
          required: {
            value: true,
            message: "Brand is required.", },
        },
        'product_form[occasion]': {
          required: {
            value: true,
            message: "Occasion is required.", },
        },
        'product_form[video][videoUrl]': {
          value: true,
          pattern: /^(https?:\/\/|www\.)[a-zA-Z0-9-]+\.[a-zA-Z]{2,6}(\/[^\s]*)?$/,
          message: "Please enter a valid URL"
        }
      },
      errorClass: "validation-error",
      validationClasses: {
        isInvalid: {
          input: "is-invalid",
          error: "invalid-feedback"
        }
      }
    };

    /* Add newly added DOM elements to the form validator.
      // NOTE: All new colors are added dynamically.
    */
    for (const i in tagSet)
      options.fields[i] = tagSet[i];

    // Initialize the validator
    let validator = new Validator(form, options);

    if (validateImage() === false)
      e.preventDefault();

    if (validator.validate() === false ||
        validateImage() === false )
    {
      /*  Expand only the sections which contain an error message */
      let collapseButtons = colorSection.querySelectorAll('[data-toggle]');
      collapseButtons.forEach((collapseButton, i) =>
      {
        let errorTarget =  document.querySelector(collapseButton.dataset.target).querySelector('.invalid-feedback');

        if (errorTarget != null)
          if (collapseButton.ariaExpanded !== 'true')
            collapseButton.click();
      });


      /* Smooth scroll to the first error message  */
      let scrollTarget = document.querySelector('.invalid-feedback').parentElement;
      if (!empty(scrollTarget))
      {
        scrollTarget.closest('div');
        smoothScroll(scrollTarget, 300);
      }

      /* Prevent form submission*/
      e.preventDefault();
    };
  });
}
