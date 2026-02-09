// assets/js/admin/product/apply-color.js


import {empty} from '../../module.js';
import Sortable from 'sortablejs';

if (document.querySelector('form[name="product_form"]') !== null)
{
  /*
    Display the color selected for each color <select> on its right side.
    Argument:
      option: The option selected.
      id: The id of the <select> being fired.
  */
  var applyColor = (select) =>
  {
    let selectParent = select.closest('[data-color-patch-parent]'),
        colorPatch = selectParent.querySelector('[data-color-patch]'),
        option = select.options[select.selectedIndex];

    // Set background image (color)
    if (!empty(option.value))
    {
      let color = option.value,
      fullColor = option.innerHTML;

      colorPatch.setAttribute('title', fullColor);
      colorPatch.style.backgroundImage = "url(" + document.location.origin + "/build/images/" + color + ".webp)";
    }
    else if (empty(option.value))
    {
      colorPatch.style.backgroundImage = "url(" + document.location.origin + "/build/images/empty-image.svg)";
      colorPatch.setAttribute('title', '');
    }
  }

  var applyColorLabel = (select) =>
  {
    let selectDiv = select.closest('[data-mono-color]'),
        pc = document.getElementById('product_form_productColor'),
        pcSet = pc.querySelectorAll('[pc-field]'),
        option = select.options[select.selectedIndex],
        selectArray = [], string;

    if (select.dataset.hasOwnProperty('colorSelect'))
    {
      pcSet.forEach((productColor, i) =>
      {
        let selectSet = productColor.querySelectorAll('select[data-color-select=""]'),
            label = productColor.querySelector('label');

        selectArray = [];
        /* Remove empty elements
             1) ''
             2) null and
             3) undefined
             4) false values from the array.
        */
        selectSet.forEach((selectTag, i) =>
        {
          selectArray.push(selectTag.options[selectTag.selectedIndex].innerHTML);
          const filtered = selectArray.filter((a) => a);
          string = filtered.join(' / ');
        });

        if (!empty(string))
          label.innerHTML = `Color ${i+1} :  ${string} `;
        else label.innerHTML = `Color ${i+1}`;
      });

      let parent = select.closest('[pc-field]'),
          patch1 = parent.querySelector('#patch-1'),
          patch2 = parent.querySelector('#patch-2'),
          patch3 = parent.querySelector('#patch-3'),
          color = '', fullColor = '';

      // Set background image (color)
      if (!empty(option.value))
      {
        color = option.value,
        fullColor = option.innerHTML;
      }

      switch (select.dataset.colorOrder)
      {
        case '1':
          if (!empty(color))
            patch1.style.backgroundImage = "url(" + document.location.origin + "/build/images/" + color + ".webp)";
          else
            patch1.style.backgroundImage = "";
          break;
        case '2':
          if (!empty(color))
            patch2.style.backgroundImage = "url(" + document.location.origin + "/build/images/" + color + ".webp)";
          else
            patch2.style.backgroundImage = "";
          break;
        case '3':
          if (!empty(color))
            patch3.style.backgroundImage = "url(" + document.location.origin + "/build/images/" + color + ".webp)";
          else
            patch3.style.backgroundImage = "";
          break;
        default:
      }
    }
  }

  var applyExcludeColor = (select) =>
  {
    let patch = select.closest('[data-color-patch-parent=""]').querySelector('[data-multi-color-patch=""]'),
    selectedColors = [], selectedPatches = [];

    patch.children.forEach((color, i) =>
    {
      selectedPatches.push(color.dataset.color);
    });

    select.selectedOptions.forEach((option, i) =>
    {
      selectedColors.push(option.value);
    });

    if (select.selectedOptions.length != 0)
    {
      patch.innerHTML = '';
      select.selectedOptions.forEach((option, i) =>
      {
        let color = document.createElement('div');
        color.style.backgroundImage = "url(" + document.location.origin + "/build/images/" + option.value + ".webp)";
        patch.appendChild(color);
        color.setAttribute('class','patch');
        color.setAttribute('title',option.innerHTML);
        color.setAttribute('data-color',option.value);
      });
    }

    if (select.selectedOptions.length == 0)
    {
      patch.innerHTML = '';
      let color = document.createElement('div');
      color.style.backgroundImage = "url(" + document.location.origin + "/build/images/empty-image.svg)";
      patch.appendChild(color);
      color.setAttribute('class','patch');
      color.setAttribute('title','');
      color.setAttribute('data-color','');
    }
  }
}

export {applyColor,applyColorLabel,applyExcludeColor};
