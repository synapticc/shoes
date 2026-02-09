// assets/js/store/phone.js

import { validateNumber } from './validate-phone.js';

if (document.querySelector('div[data-phone]') !== null)
{
    let phoneDivSet = document.querySelectorAll('div[data-phone]');
    phoneDivSet.forEach((phoneDiv, i) =>
    {
      let phoneInput =  phoneDiv.querySelector('input[data-phone-number]'),
      phoneForm = phoneDiv.closest('form');


      phoneInput.addEventListener('input', () => validateNumber(phoneDiv));
      phoneInput.addEventListener('paste', () => validateNumber(phoneDiv));

      // Prevent form submission if phone number is invalid.
      phoneForm.addEventListener('submit', e =>
      {
        if (phoneInput.classList.contains('input-is-invalid'))
          e.preventDefault();
      });
    });
}
