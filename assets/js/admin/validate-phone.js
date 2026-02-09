// assets/js/admin/validate-phone.js

import empty from 'is-blank';
import SlimSelect from 'slim-select';
import examples from 'libphonenumber-js/mobile/examples';
import { parsePhoneNumber, getExampleNumber, AsYouType,
         parsePhoneNumberWithError, ParseError}
      from 'libphonenumber-js/max';


/* Validate phone and mobile phone numbers of Admin forms */
let validateNumber = (phoneDiv) =>
{
  let phoneInput = phoneDiv.querySelector('input[data-phone-number]'),
      phoneSelect = phoneDiv.querySelector('select[data-country-code]'),
      form = phoneDiv.closest('form'),
      phoneFormat = document.getElementById('phone-format');

  let phoneType, selectedCodeOption, selectedCode,
  selectedCountryCode, fullPhoneNumber;

  if (phoneDiv.dataset.hasOwnProperty('phoneType'))
    phoneType = phoneDiv.dataset.phoneType;

  if (!empty(phoneDiv.dataset.selected))
    countryCode = phoneDiv.dataset.selected;

  if (phoneSelect.options[phoneSelect.selectedIndex])
  {
    selectedCodeOption = phoneSelect.options[phoneSelect.selectedIndex];

    // value="MU"
    selectedCode = selectedCodeOption.value;

    // Mauritius (+230)
    // Extract '(+230)' from the above string
    selectedCountryCode = `(${selectedCodeOption.innerHTML.split(/\(|\)/)[1]})`;

    // 230 + 453 4430 = 2304534430
    let onlyCode = selectedCountryCode.replace(/\D/g, '');
    fullPhoneNumber = onlyCode + phoneInput.value;
  }

  if (empty(phoneInput.value))
  {
    if (phoneInput.classList.contains('is-invalid'))
      phoneInput.classList.remove('is-invalid');

    phoneInput.setAttribute('title', 'Enter a number.');
  }

  if (!empty(selectedCode))
  {
      let phoneExample = getExampleNumber(selectedCode, examples),
      phonePlaceholder = phoneExample.formatNational(),
      maxValidNumber = phonePlaceholder.length;

      if(!empty(phoneFormat))
        phoneFormat.innerHTML = `ex. ${phonePlaceholder}`;

      if (phoneType ===  'mobile')
        phoneInput.setAttribute('placeholder', phonePlaceholder);
  }
  else
  {
    phoneFormat.innerHTML = '';
    phoneInput.setAttribute('placeholder', '');
  }

  if (!empty(phoneInput.value))
  {
    // try
    // {
    //   let phoneNumber = parsePhoneNumberWithError(fullPhoneNumber,
    //   {defaultCountry: selectedCode});
    //
    //   console.log(phoneNumber.isPossible(),phoneNumber.isValid());
    //
    //   if (phoneType === 'mobile')
    //   {
    //     if (phoneNumber.isPossible() === true)
    //     {
    //       if (phoneNumber.isValid() === true && phoneNumber.getType() == 'MOBILE')
    //       {
    //         if (phoneInput.classList.contains('is-invalid'))
    //           phoneInput.classList.replace('is-invalid', 'is-valid');
    //         else
    //           phoneInput.classList.add('is-valid');
    //
    //         phoneInput.setAttribute('title', 'Looks good!');
    //
    //         if (!(phoneInput.value.length < maxValidNumber))
    //           phoneInput.setAttribute('maxlength',phoneInput.value.length);
    //
    //         // Replace the inputed value with a formatted version
    //         let formattedInput  = new AsYouType(selectedCode).input(phoneInput.value);
    //         phoneInput.value = formattedInput;
    //
    //       }
    //       else if (phoneNumber.isValid() === false)
    //       {
    //         if (phoneInput.classList.contains('is-valid'))
    //           phoneInput.classList.replace('is-valid', 'is-invalid');
    //         else
    //           phoneInput.classList.add('is-invalid');
    //
    //         if (phoneInput.hasAttribute('maxlength'))
    //           phoneInput.setAttribute('maxlength',maxValidNumber);
    //
    //         phoneInput.setAttribute('title', 'Invalid number. Keep trying!');
    //       }
    //     }
    //     else if (phoneNumber.isPossible() === false)
    //     {
    //       if (phoneInput.classList.contains('is-valid'))
    //         phoneInput.classList.replace('is-valid', 'is-invalid');
    //       else
    //         phoneInput.classList.add('is-invalid');
    //
    //       if (phoneInput.value.length >= maxValidNumber)
    //         phoneInput.setAttribute('maxlength',maxValidNumber);
    //
    //       phoneInput.setAttribute('title', 'Invalid number. Keep trying!');
    //     }
    //   }
    //   else if (phoneType === 'landline')
    //   {
    //     if (phoneNumber.isPossible() === true)
    //     {
    //       if (phoneNumber.isValid() === true && phoneNumber.getType() == 'FIXED_LINE')
    //       {
    //         if (phoneInput.classList.contains('is-invalid'))
    //           phoneInput.classList.replace('is-invalid', 'is-valid');
    //         else
    //           phoneInput.classList.add('is-valid');
    //
    //         phoneInput.setAttribute('title', 'Looks good!');
    //
    //         if (phoneInput.hasAttribute('maxlength'))
    //           phoneInput.setAttribute('maxlength', 25);
    //
    //         // Replace the inputed value with a formatted version
    //         let formattedInput  = new AsYouType(selectedCode).input(phoneInput.value);
    //         phoneInput.value = formattedInput;
    //
    //       }
    //       else if (phoneNumber.isValid() === false)
    //       {
    //         if (phoneInput.classList.contains('is-valid'))
    //           phoneInput.classList.replace('is-valid', 'is-invalid');
    //         else
    //           phoneInput.classList.add('is-invalid');
    //
    //         if (phoneInput.hasAttribute('maxlength'))
    //           phoneInput.setAttribute('maxlength', 25);
    //
    //         phoneInput.setAttribute('title', 'Invalid number. Keep trying!');
    //       }
    //     }
    //     else if (phoneNumber.isPossible() === false)
    //     {
    //       if (phoneInput.classList.contains('is-valid'))
    //         phoneInput.classList.replace('is-valid', 'is-invalid');
    //       else
    //         phoneInput.classList.add('is-invalid');
    //
    //       if (phoneInput.hasAttribute('maxlength'))
    //         phoneInput.setAttribute('maxlength', 25);
    //
    //       phoneInput.setAttribute('title', 'Invalid number. Keep trying!');
    //     }
    //   }
    // }
    // catch (error)
    // {
    //   console.log('is-invalid');
    //   if (phoneInput.classList.contains('is-valid'))
    //     phoneInput.classList.replace('is-valid', 'is-invalid');
    //   else
    //     phoneInput.classList.add('is-invalid');
    //
    //   if (phoneInput.hasAttribute('maxlength'))
    //     phoneInput.setAttribute('maxlength', 25);
    //
    //   phoneInput.setAttribute('title', 'Invalid number. Keep trying!');
    //   // if (error instanceof ParseError)
    //   //   console.log(error.message);
    //   //  else throw error
    // }
  }
}

export {validateNumber};
