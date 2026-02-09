// assets/js/countries-zip.js

import SlimSelect from 'slim-select';
import empty from 'is-blank';
import examples from 'libphonenumber-js/mobile/examples';
import { parsePhoneNumber, getExampleNumber, AsYouType,
         parsePhoneNumberWithError, ParseError}
      from 'libphonenumber-js/max';

// 25 random cities per country led by the capital | 5148 cities (fast loading)
import countries from '../plugins/admin/cities/cities_25.json';

// All cities of the world | 154, 694 cities (slow loading)
// import countries from '../../plugins/admin/cities/cities.json';


if (document.querySelector('select[data-country]') !== null)
{
    let countrySelect = document.querySelector('select[data-country]'),
    form = countrySelect.closest('form'),
    citySelect = form.querySelector('select[data-city]'),
    phoneCodeSet = form.querySelectorAll('select[data-country-code]'),
    phoneInputSet = form.querySelectorAll('input[data-phone-number]'),
    phoneDivSet = form.querySelectorAll('div[data-phone]'),
    phoneFormat = document.getElementById('phone-format'),
    smartphoneIcon = document.getElementById('smartphone-icon'),
    oldPhoneIcon = document.getElementById('old-phone-icon'),
    cities = [], citySelected = '', citiesKeysPair = [],
    phoneSelectArray = [], countryCodeSelected, formType, phoneType;


    if (form.dataset.hasOwnProperty('form'))
      formType = form.dataset.form;

    let countrySlimSelect =  new SlimSelect({
        select: '#' + countrySelect.id,
        settings: {
          allowDeselect: true,
          placeholderText: 'country',
        }
      });

    phoneCodeSet.forEach((phoneCode, j) =>
    {
      let phoneCodeSlimSelect = new SlimSelect({
          select: `#${phoneCode.id}`,
          settings: {
            allowDeselect: true,
            placeholderText: 'code',
          },
          events: {
            afterChange: (option) =>
            {
              if (!empty(option[0].value))
              {
                countrySlimSelect.setSelected(option[0].value);

                phoneInputSet.forEach((phoneInput, j) =>
                {
                  let phoneSelect = document.getElementById(phoneInput.dataset.target),
                  selectedCode = phoneSelect.options[phoneSelect.selectedIndex].value;

                  if (phoneInput.dataset.hasOwnProperty('phoneType'))
                    phoneType = phoneInput.dataset.phoneType;

                  let phoneExample = getExampleNumber(selectedCode, examples),
                  phonePlaceholder = phoneExample.formatNational(),
                  maxValidNumber = phonePlaceholder.length;

                  if(!empty(phoneFormat))
                    phoneFormat.innerHTML = `ex. ${phonePlaceholder}`;

                  if (phoneType ===  'mobile')
                    phoneInput.setAttribute('placeholder', phonePlaceholder);

                  if (phoneInput.hasAttribute('disabled'))
                    phoneInput.removeAttribute('disabled');
                });
              }
              else
              {
                countrySlimSelect.setSelected('');
                phoneInputSet.forEach((phoneInput, j) =>
                {
                  if (phoneInput.dataset.hasOwnProperty('phoneType'))
                    phoneType = phoneInput.dataset.phoneType;

                  phoneInput.setAttribute('value','');
                  phoneInput.value = '';

                  if (formType == 'admin')
                  {
                    if (phoneInput.classList.contains('is-invalid'))
                      phoneInput.classList.remove('is-invalid');

                    if (phoneInput.classList.contains('is-valid'))
                      phoneInput.classList.remove('is-valid');

                    phoneFormat.innerHTML = '';
                  }
                  else if (formType == 'store')
                  {
                    if (phoneType === 'mobile')
                      if (smartphoneIcon.classList.contains('mobile-valid'))
                        smartphoneIcon.classList.replace('mobile-valid', 'mobile-invalid');

                    if (phoneType === 'landline')
                      if (oldPhoneIcon.classList.contains('phone-valid'))
                        oldPhoneIcon.classList.replace('phone-valid', 'phone-invalid');

                    phoneFormat.innerHTML = '';
                  }

                  phoneInput.setAttribute('title', 'Choose a country first.');
                  phoneInput.setAttribute('placeholder', '');
                  phoneInput.setAttribute('disabled', '');
                });
              }
            }},
        });

      if (!empty(phoneCode.dataset.selected))
      {
        // let code = phoneCode.dataset.selected;
        // phoneCodeSlimSelect.setSelected(code);
      }
      else
      {
        phoneCodeSlimSelect.setSelected('');
      }

      phoneSelectArray.push(phoneCodeSlimSelect);

      let phoneInput = document.getElementById(phoneCode.dataset.target);
      if (empty(phoneInput.value))
      {
        if (!empty(phoneCode.dataset.selected))
        {
          let selectedCode = phoneCode.dataset.selected;
          phoneCodeSlimSelect.setSelected(selectedCode);

          if (phoneCode.dataset.phoneType ===  'mobile')
          {
            let phoneExample = getExampleNumber(selectedCode, examples),
            phonePlaceholder = phoneExample.formatNational(),
            maxValidNumber = phonePlaceholder.length;

            phoneInput.setAttribute('placeholder', phonePlaceholder);

            if(!empty(phoneFormat))
              phoneFormat.innerHTML = `ex. ${phonePlaceholder}`;
          }
        }
      }
    });

    if (!empty(countrySlimSelect.getSelected()[0]))
      cities = countries[(countrySlimSelect.getSelected()[0])];
    else if (empty(countrySlimSelect.getSelected()[0]))
      cities = countries['MU'];

    for (var city of cities)
    {
      citiesKeysPair.push({
        text: city,
        value: city
      });
    }

    let citySlimSelect =  new SlimSelect({
        select: '#' + citySelect.id,
        settings: {
          allowDeselect: true,
          placeholderText: 'city',
        },
        data: citiesKeysPair,
      });

    if (!empty(citySelect.dataset.selected))
    {
      citySelected = citySelect.dataset.selected;
      citySlimSelect.setSelected(citySelected);
    }
    else
    {
      citySlimSelect.setSelected('');
    }

    countrySelect.addEventListener('change', e =>
    {
        let selectedCountry = countrySlimSelect.getSelected()[0],
        cities = countries[(countrySlimSelect.getSelected()[0])],
        citiesKeysPair = [];

        if (!empty(selectedCountry))
        {
          for (var city of cities)
          {
            citiesKeysPair.push({
              text: city,
              value: city
            });
          }
          citySlimSelect.setData(citiesKeysPair, false);
        }
        else
        {
          citySlimSelect.setData([]);
        }

        if (!empty(citySelect.dataset.selected))
        {
          citySelected = citySelect.dataset.selected;
          citySlimSelect.setSelected(citySelected);
        }
        else
        {
          citySlimSelect.setSelected('');
        }

        phoneSelectArray.forEach((phoneSlimSelect, i) =>
        {
          phoneSlimSelect.setSelected(selectedCountry);
        });

        phoneInputSet.forEach((phoneInput, j) =>
        {
          if (!empty(phoneInput.dataset.originalNumber))
          {
            let originalNumber = phoneInput.dataset.originalNumber.replace(/\s+/g, ''),
            currentNumber = phoneInput.value.replace(/\s+/g, ''),
            originalCountry = phoneInput.dataset.originalCountry;

            if (phoneInput.dataset.hasOwnProperty('phoneType'))
              phoneType = phoneInput.dataset.phoneType;

            if (!((originalNumber == currentNumber) &&
                (selectedCountry == originalCountry)))
            {
              phoneInput.setAttribute('value','');
              phoneInput.value = '';

              if (formType == 'admin')
              {
                if (phoneInput.classList.contains('is-invalid'))
                  phoneInput.classList.remove('is-invalid');

                if (phoneInput.classList.contains('is-valid'))
                  phoneInput.classList.remove('is-valid');

                phoneFormat.innerHTML = '';
              }
              else if (formType == 'store')
              {
                if (phoneType === 'mobile')
                  if (smartphoneIcon.classList.contains('mobile-valid'))
                    smartphoneIcon.classList.replace('mobile-valid', 'mobile-invalid');

                if (phoneType === 'landline')
                  if (oldPhoneIcon.classList.contains('phone-valid'))
                    oldPhoneIcon.classList.replace('phone-valid', 'phone-invalid');

                phoneFormat.innerHTML = '';
              }

              phoneInput.setAttribute('title', 'Enter a number.');
              phoneInput.setAttribute('placeholder', '');
            }
          }
        });
    });

    phoneInputSet.forEach((phoneInput, j) =>
    {
      let phoneSelect = document.getElementById(phoneInput.dataset.target);

      let selectedCodeOption, selectedCode,
      selectedCountryCode, fullPhoneNumber, countryCode;

      if (phoneInput.dataset.phoneType === 'mobile' &&
         !empty(phoneSelect.dataset.selected))
      {
        let countryCode = phoneSelect.dataset.selected,
        phoneExample = getExampleNumber(countryCode, examples),
        phonePlaceholder = phoneExample.formatNational(),
        maxValidNumber = phonePlaceholder.length;

        phoneInput.setAttribute('placeholder', phonePlaceholder);

        if(!empty(phoneFormat))
          phoneFormat.innerHTML = `ex. ${phonePlaceholder}`;
      }

      phoneInput.addEventListener('input', (e) =>
      {
        let phoneType;
        if (phoneInput.dataset.hasOwnProperty('phoneType'))
          phoneType = phoneInput.dataset.phoneType;

        if (phoneSelect.options[phoneSelect.selectedIndex])
        {
          selectedCodeOption = phoneSelect.options[phoneSelect.selectedIndex];

          // value="MU"
          selectedCode = selectedCodeOption.value;

          // Mauritius (+230)
          // Extract '+230' from the above string
          selectedCountryCode = selectedCodeOption.innerHTML.split(' ')[1];

          // +230 + 453 4430 = 2304534430
          fullPhoneNumber = selectedCountryCode + phoneInput.value;
        }

        let phoneExample = getExampleNumber(selectedCode, examples),
        phonePlaceholder = phoneExample.formatNational(),
        maxValidNumber = phonePlaceholder.length;

        if (!empty(phoneInput.value))
        {
          try
          {
            let phoneNumber = parsePhoneNumberWithError(phoneInput.value,
            {defaultCountry: selectedCode});

            if (phoneType === 'mobile')
            {
              if (phoneNumber.isPossible() === true)
              {
                if (phoneNumber.isValid() === true && phoneNumber.getType() == 'MOBILE')
                {
                  if (formType == 'admin')
                  {
                    if (phoneInput.classList.contains('is-invalid'))
                      phoneInput.classList.replace('is-invalid', 'is-valid');
                    else
                      phoneInput.classList.add('is-valid');
                  }
                  else if (formType == 'store')
                  {
                    if (phoneType === 'mobile')
                      if (smartphoneIcon.classList.contains('mobile-invalid'))
                        smartphoneIcon.classList.replace('mobile-invalid', 'mobile-valid');

                    if (phoneType === 'landline')
                      if (oldPhoneIcon.classList.contains('phone-invalid'))
                        oldPhoneIcon.classList.replace('phone-invalid', 'phone-valid');
                  }

                  phoneInput.setAttribute('title', 'Looks good!');

                  if (!(phoneInput.value.length < maxValidNumber))
                    phoneInput.setAttribute('maxlength',phoneInput.value.length);

                  // Replace the inputed value with a formatted version
                  let formattedInput  = new AsYouType(selectedCode).input(phoneInput.value);
                  phoneInput.value = formattedInput;

                }
                else if (phoneNumber.isValid() === false)
                {
                  if (formType == 'admin')
                  {
                    if (phoneInput.classList.contains('is-valid'))
                      phoneInput.classList.replace('is-valid', 'is-invalid');
                    else
                      phoneInput.classList.add('is-invalid');
                  }
                  else if (formType == 'store')
                  {
                    if (phoneType === 'mobile')
                      if (smartphoneIcon.classList.contains('mobile-valid'))
                        smartphoneIcon.classList.replace('mobile-valid', 'mobile-invalid');

                    if (phoneType === 'landline')
                      if (oldPhoneIcon.classList.contains('phone-valid'))
                        oldPhoneIcon.classList.replace('phone-valid', 'phone-invalid');
                  }

                  if (phoneInput.hasAttribute('maxlength'))
                    phoneInput.setAttribute('maxlength',maxValidNumber);

                  phoneInput.setAttribute('title', 'Invalid number. Keep trying!');
                }
              }
              else if (phoneNumber.isPossible() === false)
              {
                if (formType == 'admin')
                {
                  if (phoneInput.classList.contains('is-valid'))
                    phoneInput.classList.replace('is-valid', 'is-invalid');
                  else
                    phoneInput.classList.add('is-invalid');
                }
                else if (formType == 'store')
                {
                  if (phoneType === 'mobile')
                    if (smartphoneIcon.classList.contains('mobile-valid'))
                      smartphoneIcon.classList.replace('mobile-valid', 'mobile-invalid');

                  if (phoneType === 'landline')
                    if (oldPhoneIcon.classList.contains('phone-valid'))
                      oldPhoneIcon.classList.replace('phone-valid', 'phone-invalid');
                }

                if (phoneInput.value.length >= maxValidNumber)
                  phoneInput.setAttribute('maxlength', maxValidNumber);

                phoneInput.setAttribute('title', 'Invalid number. Keep trying!');
              }
            }
            else if (phoneType === 'landline')
            {
              if (phoneNumber.isPossible() === true)
              {
                if (phoneNumber.isValid() === true && phoneNumber.getType() == 'FIXED_LINE')
                {
                  if (formType == 'admin')
                  {
                    if (phoneInput.classList.contains('is-invalid'))
                      phoneInput.classList.replace('is-invalid', 'is-valid');
                    else
                      phoneInput.classList.add('is-valid');
                  }
                  else if (formType == 'store')
                  {
                    if (phoneType === 'mobile')
                      if (smartphoneIcon.classList.contains('mobile-invalid'))
                        smartphoneIcon.classList.replace('mobile-invalid', 'mobile-valid');

                    if (phoneType === 'landline')
                      if (oldPhoneIcon.classList.contains('phone-invalid'))
                        oldPhoneIcon.classList.replace('phone-invalid', 'phone-valid');
                  }

                  phoneInput.setAttribute('title', 'Looks good!');

                  if (phoneInput.hasAttribute('maxlength'))
                    phoneInput.setAttribute('maxlength', 25);

                  // Replace the inputed value with a formatted version
                  let formattedInput  = new AsYouType(selectedCode).input(phoneInput.value);
                  phoneInput.value = formattedInput;

                }
                else if (phoneNumber.isValid() === false)
                {
                  if (formType == 'admin')
                  {
                    if (phoneInput.classList.contains('is-valid'))
                      phoneInput.classList.replace('is-valid', 'is-invalid');
                    else
                      phoneInput.classList.add('is-invalid');
                  }
                  else if (formType == 'store')
                  {
                    if (phoneType === 'mobile')
                      if (smartphoneIcon.classList.contains('mobile-valid'))
                        smartphoneIcon.classList.replace('mobile-valid', 'mobile-invalid');

                    if (phoneType === 'landline')
                      if (oldPhoneIcon.classList.contains('phone-valid'))
                        oldPhoneIcon.classList.replace('phone-valid', 'phone-invalid');
                  }

                  if (phoneInput.hasAttribute('maxlength'))
                    phoneInput.setAttribute('maxlength', 25);

                  phoneInput.setAttribute('title', 'Invalid number. Keep trying!');
                }
              }
              else if (phoneNumber.isPossible() === false)
              {
                if (formType == 'admin')
                {
                  if (phoneInput.classList.contains('is-valid'))
                    phoneInput.classList.replace('is-valid', 'is-invalid');
                  else
                    phoneInput.classList.add('is-invalid');
                }
                else if (formType == 'store')
                {
                  if (phoneType === 'mobile')
                    if (smartphoneIcon.classList.contains('mobile-valid'))
                      smartphoneIcon.classList.replace('mobile-valid', 'mobile-invalid');

                  if (phoneType === 'landline')
                    if (oldPhoneIcon.classList.contains('phone-valid'))
                      oldPhoneIcon.classList.replace('phone-valid', 'phone-invalid');
                }

                if (phoneInput.hasAttribute('maxlength'))
                  phoneInput.setAttribute('maxlength', 25);

                phoneInput.setAttribute('title', 'Invalid number. Keep trying!');
              }
            }
          }
          catch (error)
          {
            if (formType == 'admin')
            {
              if (phoneInput.classList.contains('is-valid'))
                phoneInput.classList.replace('is-valid', 'is-invalid');
              else
                phoneInput.classList.add('is-invalid');
            }
            else if (formType == 'store')
            {
              if (phoneType === 'mobile')
                if (smartphoneIcon.classList.contains('mobile-valid'))
                  smartphoneIcon.classList.replace('mobile-valid', 'mobile-invalid');

              if (phoneType === 'landline')
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
      });
    });
}
