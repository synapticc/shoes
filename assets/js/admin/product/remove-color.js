// assets/js/admin/product/remove-color.js

import htmx from "htmx.org";
import empty from 'is-blank';
import {excludeColor} from './exclude-color.js';
import {resetExcludeColor} from './reset-exclude-color.js';
import {resetOtherColors} from './reset-other-colors.js';
import {sliderCount} from './slider-count.js';

if (document.querySelector('form[name="product_form"]') !== null)
{
  /* Remove color select on clicking button */
  document.addEventListener('click', e =>
  {
    if(e.target.dataset.removeColor == '')
    {
      let colorDelete = e.target,
      pc = document.getElementById('product_form_productColor');
      colorDelete.closest('[pc-field=""]').remove();


      if (pc.childElementCount > 0)
      {
        pc.children.forEach((colorSelect, pcCount) =>
        {
          let selectSet = colorSelect.querySelectorAll('select'),
              inputSet = colorSelect.querySelectorAll('input'),
              labelSet = colorSelect.querySelectorAll('label'),
              imageInputSet = colorSelect.querySelectorAll('input[type="file"]'),
              label = colorSelect.querySelector('label'),
              arrowButtons = colorSelect.querySelectorAll('#arrow-button');


          label.innerText = 'Color ' + (pcCount+1);

          /* Replace first occurrence of digit with pcCount
            name="product_form[productColor][3][color1]" */
          let t=0;
          let replaceFirstNumber = (element) =>
          {
            if (element.hasAttribute('id'))
              element.attributes.id.value =
              element.attributes.id.value.replace(/\d/gi, match => ++t === 1 ? pcCount : match);

            t = 0;

            if (element.hasAttribute('name'))
              element.attributes.name.value =
              element.attributes.name.value.replace(/\d/gi, match => ++t === 1 ? pcCount : match);

            t = 0;

            if (element.hasAttribute('for'))
              element.attributes.for.value =
              element.attributes.for.value.replace(/\d/gi, match => ++t === 1 ? pcCount : match);

            t = 0;

            if (element.hasAttribute('data-collapse-btn'))
            {
              let target = colorSelect.querySelector(element.dataset.collapseTarget);
              element.attributes['data-target'].value =
              element.attributes['data-target'].value.replace(/\d/gi, match => ++t === 1 ? pcCount : match);

              t = 0;

              target.attributes.id.value =
              target.attributes.id.value.replace(/\d/gi, match => ++t === 1 ? pcCount : match);
            }
          }

          inputSet.forEach((input, i) =>
          {
            if (input.hasAttribute('id'))
              replaceFirstNumber(input);
          });

          labelSet.forEach((label, i) =>
          {
            replaceFirstNumber(label);
          });

          selectSet.forEach((select, i) =>
          {
            replaceFirstNumber(select);
          });

          arrowButtons.forEach((arrowButton, i) =>
          {
            replaceFirstNumber(arrowButton);
          });

          // imageInputSet.forEach((imageInput, imageInputCount) =>
          // {
          //   let t = 0;
          //
          //   // Replace first occurrence of digit with pcCount
          //   // name="product_form[productColor][3][image1]"
          //   imageInput.attributes.id.value = imageInput.attributes.id.value.replace(/\d/gi, match => ++t === 1 ? pcCount : match);
          //   t = 0;
          //
          //   // Replace second occurrence of digit with imageInputCount
          //   imageInput.attributes.id.value = imageInput.attributes.id.value.replace(/\d/gi, match => ++t === 2 ? imageInputCount+1 : match);
          //   t = 0;
          //
          //   // Replace first occurrence of digit with pcCount
          //   imageInput.attributes.name.value = imageInput.attributes.name.value.replace(/\d/gi, match => ++t === 1 ? pcCount : match);
          //   t = 0;
          //
          //     // Replace second occurrence of digit with imageInputCount
          //   imageInput.attributes.name.value = imageInput.attributes.name.value.replace(/\d/gi, match => ++t === 2 ? imageInputCount+1 : match);
          //
          // });
        });
      }
    }
  });

  /* Clear search
   */
  document.addEventListener('click', e =>
  {
    if(e.target.dataset.clearSearch == '')
    {
        let btn = e.target,
            results =  btn.closest('[data-results]'),
            search =  btn.closest('[data-parent]').querySelector('input');

        results.innerHTML = '';
        search.value = '';
        search.setAttribute('placeholder', 'Search');
    }
  });

  /* Remove new other product color
   * Recalibrate id, name and label
   */
  document.addEventListener('click', e =>
  {
    if(e.target.dataset.remove == '')
    {
      let btn = e.target,
          parent =  btn.closest('[data-parent]'),
          main = btn.closest('[data-main]');

      parent.remove();

      if (main.hasAttribute('data-main'))
      {
        if (main.dataset.main == 'products')
        {
          if (main.childElementCount > 0)
          {
            main.children.forEach((product, count) =>
            {
              let label = product.querySelector('label'),
                  select = product.querySelector('select'),
                  input = product.querySelector('input'),
                  result = product.querySelector('[data-results]'),
                  first = select.id.match(/\d/g)[0];

              label.innerText = 'Product ' + (count+1);

              input.attributes.id.value = input.attributes.id.value.replace(/\d/gi, count);
              input.attributes['hx-target'].value = input.attributes['hx-target'].value.replace(/\d/gi, count);
              input.attributes['hx-target'].value = input.attributes['hx-target'].value.replace(/\d/gi, count);

              result.attributes.id.value = result.attributes.id.value.replace(/\d/gi, count);
              select.attributes.name.value = select.attributes.name.value.replace(/\d/gi, count);
              select.attributes.id.value = select.attributes.id.value.replace(/\d/gi, count);

            });
          }
        }
      }
    }
  });


  /* Remove new other product
   * Recalibrate id, name and label
   */
  document.addEventListener('click', e =>
  {
    if(e.target.dataset.removeSimilarProduct == '')
    {
        let btn = e.target,
            parent =  btn.closest('[data-parent]'),
            colorTags = parent.querySelectorAll('[data-color-id]'),
            grid = document.getElementById('gridProduct'),
            gridInputSet = grid.querySelectorAll(`input`),
            similarProducts = document.getElementById('product_form_similarProduct_otherProducts'),
            colorIdSet = [];

        colorTags.forEach((colorTag, i) =>
        {
          colorIdSet.push(colorTag.dataset.colorId);

        });

        gridInputSet.forEach((gridInput, i) =>
        {
          if (colorIdSet.includes(gridInput.value))
          {
            /* Remove corresponding color grids from Grid Slider*/
              gridInput.closest('.grid-square').remove();
          }
        });

        sliderCount();
        parent.remove();


        if (similarProducts.childElementCount > 0)
        {
          similarProducts.children.forEach((similarProduct, i) =>
          {
            let t = 0,
            label = similarProduct.querySelector('label'),
            input = similarProduct.querySelector('input'),
            result = similarProduct.querySelector('[data-results]')
            ;

            label.innerText = `Product ${i+1}`;

            input.attributes.id.value = input.attributes.id.value.replace(/\d/gi, match => ++t === 1 ? i : match);
            t=0;

            input.attributes['hx-target'].value = input.attributes['hx-target'].value.replace(/\d/gi, match => ++t === 1 ? i : match);t=0;

            result.attributes.id.value = result.attributes.id.value.replace(/\d/gi, match => ++t === 1 ? i : match);t=0;

          });
        }
        htmx.process(parent);
    }
  });

  /* Remove new other product color
   * Recalibrate id, name and label
   */
  document.addEventListener('click', e =>
  {
    if(e.target.dataset.removePc == '')
    {
        let btn = e.target,
            parent =  btn.closest('[data-pc]'),
            parentDiv,
            exclude = 0;

        if (!empty(btn.closest('div[data-product-colors=""]')))
        {
          parentDiv = btn.closest('div[data-product-colors=""]');
          let color = parent.dataset.color,
          main = parent.closest('section[similar-parent]'),
          grid = main.querySelector('#gridColor'),
          gridInput = grid.querySelector(`input[value="${color}"]`);

          /* Remove corresponding color grids from Grid Slider*/
          gridInput.closest('.grid-square').remove();
        }
        else if (!empty(btn.closest('div[data-exclude-product-colors=""]')))
        {
          parentDiv =  btn.closest('div[data-exclude-product-colors=""]');
          exclude = 1;
        }

        parent.remove();

        if (parentDiv.childElementCount > 0)
        {
          parentDiv.children.forEach((productColor, i) =>
          {
            let t = 0,
            label = productColor.querySelector('label'),
            input = productColor.querySelector('input'),
            result = productColor.querySelector('[data-results]'),
            firstIndex = parentDiv.id.match(/\d/g)[0];

            label.innerText = `${exclude ? 'Exclude' : 'Product'} color ${i+1}`;

            input.attributes.id.value = input.attributes.id.value.replace(/\d/gi, match => ++t === 1 ? firstIndex : match);t=0;

            input.attributes.id.value = input.attributes.id.value.replace(/\d/gi, match => ++t === 2 ? i : match);t=0;

            input.attributes['hx-target'].value = input.attributes['hx-target'].value.replace(/\d/gi, match => ++t === 1 ? firstIndex : match);t=0;

            input.attributes['hx-target'].value = input.attributes['hx-target'].value.replace(/\d/gi, match => ++t === 2 ? i : match);t=0;

            result.attributes.id.value = result.attributes.id.value.replace(/\d/gi, match => ++t === 1 ? firstIndex : match);t=0;

            result.attributes.id.value = result.attributes.id.value.replace(/\d/gi, match => ++t === 2 ? i : match);

          });
        }
    }
  });

  /* Remove new other color
   * Recalibrate id, name and label
   */
  document.addEventListener('click', e =>
  {
    if(e.target.dataset.removeOtherColor == '')
    {
      let removeBtn = e.target,
      parent = removeBtn.closest('div[data-color-patch-parent]'),
      qty = parent.querySelector('input'),
      select = parent.querySelector('select'),
      color = select.options[select.selectedIndex],
      label = color.value,
      main = parent.closest('section[similar-parent]'),
      grid = main.querySelector('#gridColor'),
      gridInputSet = grid.querySelectorAll(`input[value="${label}"]`);

      /* Remove  corresponding color grids from Grid Slider*/
      gridInputSet.forEach((gridInput, count) =>
      {
        gridInput.closest('.grid-square').remove();
      });

    }

    if(e.target.dataset.removeOtherColor == ''
       || e.target.dataset.removeExcludeColor == '')
    {
      let btn = e.target, otherColors, newColor, newColorParent;

      if(e.target.dataset.removeOtherColor == '')
      {  newColor =  btn.closest('div[data-new-other-color=""]'),
         newColorParent =  btn.closest('div[data-other-colors=""]');}

      if(e.target.dataset.removeExcludeColor == '')
      {  newColor =  btn.closest('div[data-new-exclude-color=""]'),
         newColorParent =  btn.closest('div[data-exclude-colors=""]');}

      newColor.remove();

      resetExcludeColor(e.target);
      resetOtherColors(e.target.dataset.otherColors);

      if (newColorParent.childElementCount > 0)
      {
        newColorParent.children.forEach((color, i) =>
        {
          let t = 0,
              label = color.querySelector('label'),
              select = color.querySelector('select'),
              input = color.querySelector('input');

          label.innerText = 'Color ' + (i+1);

          input.attributes.name.value = input.attributes.name.value.replace(/\d/gi, match => ++t === 2 ? i : match);
          t=0;

          input.attributes.id.value = input.attributes.id.value.replace(/\d/gi, match => ++t === 2 ? i : match);
          t=0;

          select.attributes.name.value = select.attributes.name.value.replace(/\d/gi, match => ++t === 2 ? i : match);
          t=0

          select.attributes.id.value = select.attributes.id.value.replace(/\d/gi, match => ++t === 2 ? i : match);
          t=0;

          select.attributes['data-input'].value = select.attributes['data-input'].value.replace(/\d/gi, match => ++t === 2 ? i : match);
          t=0;
        });
      }
    }
  });
}
