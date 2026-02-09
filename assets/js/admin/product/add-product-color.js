// assets/js/admin/product/add-product-color.js

import SlimSelect from 'slim-select';
import htmx from "htmx.org";
import {applyColor} from './apply-color.js';
import {addGrid} from './add-grid.js';
import {removeGrid} from './remove-grid.js';
import {excludeColor} from './exclude-color.js';
import { isEmpty as empty }  from '@zerodep/is-empty';
import {resetOtherColors} from './reset-other-colors.js';
import Sortable from 'sortablejs';

if (document.querySelector('form[name="product_form"]') !== null)
{
  /* Add new other colors or add new exclude colors
   * Recalibrate id, name and label
   */
  document.addEventListener('click', e =>
  {
    if (e.target.dataset.addOtherColors == ''
        || e.target.dataset.addExcludeColors == '')
    {
      let btn = e.target, otherColors, newColor;

      if(e.target.dataset.addOtherColors == '')
      {
        otherColors =  btn.closest('div').querySelector('[data-other-colors=""]');
        newColor = document.getElementById('oclr-prototype').innerHTML;
      }

      if(e.target.dataset.addExcludeColors == '')
      {
        otherColors =  btn.closest('div').querySelector('[data-exclude-colors=""]');
        newColor = document.getElementById('exclr-prototype').innerHTML;
      }

      let parent = btn.closest('div').querySelector('[data-other-colors=""]'),
          firstIndex = parent.id.match(/\d/g)[0];

      /* Recalibrate label */
      newColor = newColor.replace(/__name__label__/gi, `Color ${otherColors.childElementCount+1}`);
      newColor = newColor.replace(/__name_name__/gi, otherColors.childElementCount);
      newColor = newColor.replace(/__name__/gi, firstIndex);
      otherColors.insertAdjacentHTML('beforeend', newColor);

      let justAdded = otherColors.lastElementChild,
          select = justAdded.querySelector('select[data-other-color-select]'),
          otherColorSelectSet = document.getElementById(select.dataset.otherColors).querySelectorAll('select[data-other-color-select]'),
          excludeSelect = document.getElementById(select.dataset.exclude),
          excludeSelected = Array.from(excludeSelect.selectedOptions).map(option => option.value),
          otherSelected = [];


      otherColorSelectSet.forEach((otherColor, i) =>
      {
        let color = otherColor.options[otherColor.selectedIndex],
            label = color.value;
        otherSelected.push(label);
      });

      select.options.forEach((option, i) =>
      {
        if (otherSelected.includes(option.value) ||
            excludeSelected.includes(option.value))
        {
          if (!option.hasAttribute('disabled'))
            option.setAttribute('disabled','');
        }
      });

      if (select.length > 0)
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
              applyColor(select);

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

        const otherColorSlimSelect = new SlimSelect(options);
      }
    }
  });

  /* Add new product colors or add new exclude product colors
   * Recalibrate id, name and label
   */
  document.addEventListener('click', e =>
  {
    if (e.target.dataset.addProductColors == ''
        || e.target.dataset.addExcludeProductColors == '')
    {
      let btn = e.target, productColors, newProductColor, first,
      label, index, id, justAdded, target, targetColors, colorIdSet = [],
      newURL;

      productColors =  btn.closest('div').querySelector('[data-product-colors]');

      if(e.target.dataset.addProductColors == '')
      {
        newProductColor = document.getElementById('opc-prototype').innerHTML;
        label = 'Product color';
      }

      if(e.target.dataset.addExcludeProductColors == '')
      {
        newProductColor = document.getElementById('expc-prototype').innerHTML;
        label = 'Exclude color';
      }

      newURL =  productColors.dataset.id;
      target = document.getElementById(productColors.dataset.target);

      if(!empty(target.querySelectorAll('[data-color-id]')))
      {
        targetColors = target.querySelectorAll('[data-color-id]');

        targetColors.forEach((element, i) =>
        {
          colorIdSet.push(element.getAttribute(['data-color-id']));
        });
      }

      if (!empty(colorIdSet))
      {
        newURL = '?';

        for (let i = 0; i < colorIdSet.length; i++)
        {
          newURL += `exclude[]=${colorIdSet[i]}`;
          if(i !== colorIdSet.length - 1)
            newURL += '&';
        }
      }

      index = productColors.childElementCount+1;
      newProductColor = newProductColor.replace(/__name__label__/gi, `${label} ${index}`);

      first = productColors.id.match(/\d/g)[0];
      newProductColor = newProductColor.replace(/__name__/gi, first);

      index = productColors.childElementCount;
      newProductColor = newProductColor.replace(/__name_name__/gi, index);

      newProductColor = newProductColor.replace(/__color__/gi, newURL);

      productColors.insertAdjacentHTML('beforeend', newProductColor);

      // enabling htmx for the recently added elements
      justAdded = productColors.lastElementChild;
      htmx.process(justAdded);

      /* Retrieve the newly added product color */
      // let newColorDiv = productColors.lastElementChild,
      //     parent = newColorDiv.querySelector('[data-pc=""]'),
      //     first = productColors.id.match(/\d/g)[0],
      //     second = productColors.childElementCount-1,
      //     select = newColorDiv.querySelector('select'),
          // input = newColorDiv.querySelector('input');
      //     result = newColorDiv.querySelector('[data-results=""]'),
      //     t=0;

      /* Recalibrate id, name of parent <div>, <select> and <input>
       */
      // parent.attributes.id.value = parent.attributes.id.value.replace(/__name__/gi, match => ++t === 1 ? first : second);t=0;
      //
      // input.attributes.id.value = input.attributes.id.value.replace(/__name__/gi, match => ++t === 1 ? first : second);t=0;
      //
      // select.attributes.id.value = select.attributes.id.value.replace(/__name__/gi, match => ++t === 1 ? first : second);t=0;
      //
      // select.attributes.name.value = select.attributes.name.value.replace(/__name__/gi, match => ++t === 1 ? first : second);t=0;

      // input.setAttribute('name',`id`);

      // if(e.target.dataset.addProductColors == '')
      // { input.setAttribute('hx-target',`#search-results-${first}${second}`);
      //   result.setAttribute('id',`search-results-${first}${second}`);}
      // else if (e.target.dataset.addExcludeProductColors == '')
      // { input.setAttribute('hx-target',`#search-results-ex-${first}${second}`);
      //   result.setAttribute('id',`search-results-ex-${first}${second}`);}

    }
  });

  /* Add similar product color option
   * Recalibrate id, name and label
   */
  document.addEventListener('click', e =>
  {
    if(e.target.dataset.addColorOption == '' )
    {
      let btn = e.target,
      parent =  btn.closest('[data-pc]'),
      productColors =  btn.closest('[data-product-colors]').querySelectorAll('[data-color-id]'),
      searchResult =  parent.querySelector('[data-results]'),
      id = btn.dataset.pcId,
      imgURL =  btn.dataset.thumbnailUrl,
      name = btn.dataset.name,
      color = btn.dataset.color,
      fabric = btn.dataset.fabric,
      thumbnail =  parent.querySelector('[data-thumbnail]'),
      previousColor = thumbnail.dataset.previousColor,
      targetName =  parent.querySelector('[data-name-target]'),
      targetColor =  parent.querySelector('[data-color-target]'),
      targetFabric =  parent.querySelector('[data-fabric-target]'),
      selectParentRow =  parent.querySelector('[data-pc-select]'),
      similar = parent.closest('section[similar-parent]'),
      index = similar.id.match(/\d/g)[0],
      grid =  similar.querySelector('#gridColor'),
      gridInput = grid.querySelector(`input[value="${previousColor}"]`),
      colorIdSet = [];

// console.log(productColors);

    colorIdSet.push(id);

      if(!empty(productColors))
      {
        productColors.forEach((element, i) =>
        {
          colorIdSet.push(element.getAttribute(['data-color-id']));
        });
      }


      // console.log(colorIdSet);

      /* Remove corresponding color grids from Grid Slider*/
      if ((e.target.dataset.addProductColor == '') && !empty(gridInput))
        gridInput.closest('.grid-square').remove();

      const options =
      {
        animation: 150,
        ghostClass: 'blue-background-class',
        removeOnSpill: true,

        // Element dragging ended
        onEnd: function (e)
        {
          let itemEl = e.item;e.to;e.from;e.oldIndex;e.newIndex;
          e.oldDraggableIndex;e.newDraggableIndex;e.clonee.pullMode;
        },
        onRemove: function (e)
        {
        }
      };

      let sorted = new Sortable(grid,  options);


      // if (parentDiv.childElementCount > 0)
      // {
      //   parentDiv.children.forEach((productColor, i) =>
      //   {
      //     let t = 0,
      //     label = productColor.querySelector('label'),
      //     select = productColor.querySelector('select'),
      //     input = productColor.querySelector('input'),
      //     result = productColor.querySelector('div[data-results=""]'),
      //     first = select.id.match(/\d/g)[0];
      //
      //     label.innerText = 'Product color ' + (i+1);
      //
      //     input.attributes.id.value = input.attributes.id.value.replace(/\d/gi, match => ++t === 1 ? first : match);t=0;
      //
      //     input.attributes.id.value = input.attributes.id.value.replace(/\d/gi, match => ++t === 2 ? i : match);t=0;
      //
      //     input.attributes['hx-target'].value = input.attributes['hx-target'].value.replace(/\d/gi, match => ++t === 1 ? first : match);t=0;
      //
      //     input.attributes['hx-target'].value = input.attributes['hx-target'].value.replace(/\d/gi, match => ++t === 2 ? i : match);t=0;
      //
      //     result.attributes.id.value = result.attributes.id.value.replace(/\d/gi, match => ++t === 1 ? first : match);t=0;
      //
      //     result.attributes.id.value = result.attributes.id.value.replace(/\d/gi, match => ++t === 2 ? i : match);
      //
      //     t=0;
      //     select.attributes.name.value = select.attributes.name.value.replace(/\d/gi, match => ++t === 1 ? first : match);
      //
      //     t=0;
      //     select.attributes.name.value = select.attributes.name.value.replace(/\d/gi, match => ++t === 2 ? i : match);
      //
      //     t=0;
      //     select.attributes.id.value = select.attributes.id.value.replace(/\d/gi, match => ++t === 1 ? first : match);
      //
      //     t=0;
      //     select.attributes.id.value = select.attributes.id.value.replace(/\d/gi, match => ++t === 2 ? i : match);
      //   });
      // }


      thumbnail.style.backgroundImage  = "url(" + document.location.origin + imgURL + ")";
      // thumbnail.src = imgURL;
      targetName.innerHTML =  name;
      targetColor.innerHTML = color;
      targetFabric.innerHTML  = fabric;
      thumbnail.setAttribute('title', name);
      selectParentRow.setAttribute('data-color-id', id),
      selectParentRow.removeAttribute('hidden');

      if(e.target.dataset.excludeProductColor == '' )
      {
        let select =  parent.querySelector('select'),
        selectedColor = select.options[select.selectedIndex].value,
        option = new Option(name, id, true, true);

        if (select.options[0] !== undefined )
          select.remove(0);

        select.add(option,0);
      }

      searchResult.innerHTML = '';

      if(e.target.dataset.excludeProductColor != '' )
      {
        let div = document.createElement('div'),
            input = document.createElement('input'),
            span = document.createElement('span');

          div.setAttribute('class','grid-square');
          input.setAttribute('type','text');
          input.setAttribute('value', id);
          input.setAttribute('name', `product_form[colors][${index}][similarProductColor][sort][${grid.childElementCount}]`);
          input.setAttribute('hidden','');
          div.setAttribute('title', name);
          /* Display only the first color */
          span.innerHTML = color;
          div.style.backgroundImage = "url(" + document.location.origin + imgURL + ")";

          grid.appendChild(div);
          div.appendChild(input);
          div.appendChild(span);
      }
    }
  });

  // document.addEventListener('change', e =>
  // {
  //   if(e.target.dataset.otherColorSelect == '' )
  //   {
  //     // resetOtherColors(e.target.dataset.otherColors, e.target);
  //     // return true;
  //   }
  // });
}
