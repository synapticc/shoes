// assets/js/store/validate-phone.js

import empty from 'is-blank';
import SlimSelect from 'slim-select';
import examples from 'libphonenumber-js/mobile/examples';
import { parsePhoneNumber, getExampleNumber, AsYouType,
         parsePhoneNumberWithError, ParseError}
      from 'libphonenumber-js/max';


/* Validate phone and mobile phone numbers of Admin forms */
let validateNumber = (phoneDiv) =>
{
  let phoneInput =  phoneDiv.querySelector('input[data-phone-number]'),
  phoneSelect =  phoneDiv.querySelector('select[data-country-code]'),
  phoneForm = phoneDiv.closest('form'),
  smartphoneIcon = document.getElementById('smartphone-icon'),
  oldPhoneIcon = document.getElementById('old-phone-icon');

  let phoneType, selectedCodeOption, selectedCode,
  selectedCountryCode, fullPhoneNumber;

  if (phoneDiv.dataset.hasOwnProperty('phoneType'))
  phoneType = phoneDiv.dataset.phoneType;


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
    if (phoneType === 'mobile')
      if (smartphoneIcon.classList.contains('mobile-valid'))
        smartphoneIcon.classList.replace('mobile-valid', 'mobile-invalid');

    if (phoneType === 'phone')
      if (oldPhoneIcon.classList.contains('phone-valid'))
        oldPhoneIcon.classList.replace('phone-valid', 'phone-invalid');

    phoneInput.setAttribute('title', 'Enter a number.');
  }

  try
  {
    let phoneNumber = parsePhoneNumberWithError(fullPhoneNumber,
    {defaultCountry: selectedCode}),
    phoneExample = getExampleNumber(selectedCode, examples),
    phonePlaceholder = phoneExample.formatNational(),
    maxValidNumber = phonePlaceholder.length;

    if (phoneType ===  'mobile')
    {
      if (phoneNumber.isPossible() === true)
      {
        if (phoneNumber.isValid() === true && phoneNumber.getType() == 'MOBILE')
        {
          if (smartphoneIcon.classList.contains('mobile-invalid'))
            smartphoneIcon.classList.replace('mobile-invalid', 'mobile-valid');

          phoneInput.setAttribute('title', 'Looks good!');

          if (!(phoneInput.value.length < maxValidNumber))
            phoneInput.setAttribute('maxlength',phoneInput.value.length);

          // Replace the inputed value with a formatted version
          let formattedInput  = new AsYouType(selectedCode).input(phoneInput.value);
          phoneInput.value = formattedInput;
        }
        else if (phoneNumber.isValid() === false)
        {
          if (smartphoneIcon.classList.contains('mobile-valid'))
            smartphoneIcon.classList.replace('mobile-valid', 'mobile-invalid');

          if (phoneInput.hasAttribute('maxlength'))
            phoneInput.setAttribute('maxlength',maxValidNumber);

          phoneInput.setAttribute('title', 'Invalid number. Keep trying!');
        }
      }
      else if (phoneNumber.isPossible() === false)
      {
        if (smartphoneIcon.classList.contains('mobile-valid'))
          smartphoneIcon.classList.replace('mobile-valid', 'mobile-invalid');

        // if (!phoneInput.classList.contains('input-is-invalid'))
        //   phoneInput.classList.add('input-is-invalid');
        //
        // if (phoneInput.classList.contains('input-is-valid'))
        //   phoneInput.classList.replace('input-is-valid', 'input-is-invalid');

        if (phoneInput.value.length >= maxValidNumber)
          phoneInput.setAttribute('maxlength',maxValidNumber);

        phoneInput.setAttribute('title', 'Invalid number. Keep trying!');
      }
    }
    else if (phoneType === 'landline')
    {
      if (phoneNumber.isPossible() === true)
      {
        if (phoneNumber.isValid() === true && phoneNumber.getType() == 'FIXED_LINE')
        {
          if (oldPhoneIcon.classList.contains('phone-invalid'))
            oldPhoneIcon.classList.replace('phone-invalid', 'phone-valid');

          phoneInput.setAttribute('title', 'Looks good!');

          if (!(phoneInput.value.length < maxValidNumber))
            phoneInput.setAttribute('maxlength',phoneInput.value.length);

          // Replace the inputed value with a formatted version
          let formattedInput  = new AsYouType(selectedCode).input(phoneInput.value);
          phoneInput.value = formattedInput;
        }
        else if (phoneNumber.isValid() === false)
        {
          if (oldPhoneIcon.classList.contains('phone-valid'))
            oldPhoneIcon.classList.replace('phone-valid', 'phone-invalid');

          if (phoneInput.hasAttribute('maxlength'))
            phoneInput.setAttribute('maxlength',maxValidNumber);

          phoneInput.setAttribute('title', 'Invalid number. Keep trying!');
        }
      }
      else if (phoneNumber.isPossible() === false)
      {
        if (oldPhoneIcon.classList.contains('phone-valid'))
          oldPhoneIcon.classList.replace('phone-valid', 'phone-invalid');

        if (phoneInput.value.length >= maxValidNumber)
          phoneInput.setAttribute('maxlength',maxValidNumber);

        phoneInput.setAttribute('title', 'Invalid number. Keep trying!');
      }
    }
  }
  catch (error)
  {
    if (phoneType ===  'mobile')
    {
      if (smartphoneIcon.classList.contains('mobile-valid'))
        smartphoneIcon.classList.replace('mobile-valid', 'mobile-invalid');
    }
    else if (phoneType === 'landline')
    {
      if (oldPhoneIcon.classList.contains('phone-valid'))
        oldPhoneIcon.classList.replace('phone-valid', 'phone-invalid');
    }

    if (phoneInput.hasAttribute('maxlength'))
      phoneInput.setAttribute('maxlength', 25);

    phoneInput.setAttribute('title', 'Invalid number. Keep trying!');
    // if (error instanceof ParseError)
    //   console.log(error.message);
    //  else throw error
  }
}

export {validateNumber};
