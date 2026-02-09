// assets/js/admin/product/color-selects.js

import SlimSelect from 'slim-select';
import htmx from "htmx.org";
import {addGrid} from './add-grid.js';
import {removeGrid} from './remove-grid.js';
import {excludeColor} from './exclude-color.js';
import {resetOtherColors} from './reset-other-colors.js';
import Sortable from 'sortablejs';
import empty from 'is-blank';
import {applyColor, applyColorLabel, applyExcludeColor} from './apply-color.js';

if (document.getElementById('product_form_productColor') !== null)
{
  let colorSelectSet = document.querySelectorAll('[data-onload-other-color-select]');

  colorSelectSet.forEach((select, i) =>
  {
    let options =
    {
      select: '#' + select.id,
      settings:
      {
        allowDeselect: false,
        placeholderText: 'Choose color',
      },
      events:
      {
        beforeChange: (option, oldOption) =>
        {
          if(!empty(oldOption[0].value))
            removeGrid(oldOption[0].value, select.id);

          return true;
        },
        afterChange: (option) =>
        {
          // applyColor(option, select.id);
          // applyColor(select);

          if (select.dataset.hasOwnProperty('excludeColor'))
            excludeColor(select);

          /* Set quantity to at least 1 once a color is chosen */
          // let qty = select.closest('[data-color-patch-parent]').querySelector('input');
          let qty = document.getElementById(select.dataset.input);
          if (!empty(option))
          {
            qty.setAttribute('value',1);
            addGrid(qty);
          }
          else {qty.setAttribute('value','');}

          resetOtherColors(select.dataset.otherColors);

          return true;
        }
      }
    };

    let colorSlimSelect = new SlimSelect(options);
  });



  document.addEventListener('change', e =>
  {

    if(e.target.dataset.applyColor === '')
    {
      let select = e.target;

      applyColor(select);
      applyColorLabel(select);
    }

  });
}
