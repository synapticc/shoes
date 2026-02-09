// assets/js/admin/product/reset-other-colors.js

import { isEmpty as empty }  from '@zerodep/is-empty';

function resetOtherColors(id)
{
  let selectSet = document.getElementById(id).querySelectorAll('select'),
      selectedColors = [];

  selectSet.forEach((element, i) =>
  {
    let color = element.options[element.selectedIndex],
        label = color.value;
    selectedColors.push(label);
  });

  selectSet.forEach((select, i) =>
  {
    let selected = select.options[select.selectedIndex];
    select.options.forEach((option, i) =>
    {
      if (selected.value != option.value)
      {
        if (selectedColors.includes(option.value))
        {
          if (!option.hasAttribute('disabled'))
            option.setAttribute('disabled','');
        }
        else {
          if (option.hasAttribute('disabled'))
            option.removeAttribute('disabled');
        }
      }
    });
  });
}

export {resetOtherColors};
