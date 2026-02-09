// assets/js/admin/product/add-products.js

import htmx from "htmx.org";
import {sliderCount} from './slider-count.js';

/* Add new input fields for features */
if (document.getElementById('add-products') !== null)
{
    let addProductsBtn = document.getElementById('add-products'),
        products = document.getElementById('product_form_similarProduct_otherProducts');

    addProductsBtn.addEventListener('click', () =>
    {
      // let newProduct = products.dataset.prototype;
      let newProduct = document.getElementById('sm-prototype').innerHTML;

      if (products.childElementCount == 0)
      { newProduct = newProduct.replace(/__name__label__/gi, 'Product 1');
        newProduct = newProduct.replace(/__name__/gi, 0);
      }
      else
      { newProduct = newProduct.replace(/__name__label__/gi, 'Product ' +  (products.childElementCount + 1));
        newProduct = newProduct.replace(/__name__/gi, products.childElementCount);}

      products.insertAdjacentHTML('beforeend',newProduct);

      /* enabling htmx for the recently added elements */
      let recent = products.lastElementChild;
      htmx.process(recent);

    });

  document.addEventListener('click', e =>
  {
    if(e.target.id == 'add-product-btn')
    {
      let btn = e.target,
          id = btn.dataset.productId,
          name = btn.dataset.productName,
          link = btn.dataset.link,
          parent =  btn.closest('[data-product]'),
          input =  btn.closest('[data-search-input]'),
          nameTarget =  parent.querySelector('[data-product-target]'),
          linkTarget =  parent.querySelector('[data-anchor-target]'),
          thumbnailRow =  parent.querySelector('[data-product-row]'),
          thumbnailParent =  parent.querySelector('[data-thumbnail]'),
          searchResultDiv =  parent.querySelector('[data-results]'),
          images =  searchResultDiv.querySelectorAll('figure'),
          grid = document.getElementById('gridProduct'),
          gridInputSet = grid.querySelectorAll(`input`),
          colorIdSet = []
          ;

      /* Make thumbnails and <select> row visible */
      if (thumbnailRow.hasAttribute('hidden'))
        thumbnailRow.removeAttribute('hidden');

      if (thumbnailParent.childElementCount == 0)
      {
        images.forEach((img, i) =>
        {
          let caption = img.querySelector('figcaption');

          if (caption.hasAttribute('hidden'))
            caption.removeAttribute('hidden');

          thumbnailParent.appendChild(img);
        });
      }
      else if (thumbnailParent.childElementCount > 0)
      {
        let colorTags = thumbnailParent.querySelectorAll('[data-color-id]');

        colorTags.forEach((colorTag, i) =>
        {
          colorIdSet.push(colorTag.dataset.colorId);
        });

        gridInputSet.forEach((gridInput, i) =>
        {
          /* Remove corresponding color grids from Grid Slider*/
          if (colorIdSet.includes(gridInput.value))
            gridInput.closest('.grid-square').remove();
        });

        thumbnailParent.innerHTML = '';

        images.forEach((img, i) =>
        {
          let caption = img.querySelector('figcaption');

          if (caption.hasAttribute('hidden'))
            caption.removeAttribute('hidden');

          thumbnailParent.appendChild(img);
        });
      }

      nameTarget.innerHTML = name;
      linkTarget.href = link;

      /* Empty search row */
      searchResultDiv.innerHTML = '';

      let similarProduct = document.getElementById('product_form_similarProduct'),
          qtySet = similarProduct.querySelectorAll('input[type="number"]'),
          max = document.getElementById('product_form_similarProduct_qtySlider'),
          thumbnails =  parent.querySelectorAll('.thumbnail');

      if (grid.children.length == 0)
      {
        qtySet.forEach((cell, count) =>
        {
          // let length = cell.closest('.row').querySelector('select').selectedOptions.length;

          let length = 0;
          if (cell.dataset.hasOwnProperty('select'))
            length = document.getElementById(cell.dataset.select).selectedOptions.length;
          else if (!cell.dataset.hasOwnProperty('select'))
            length = cell.valueAsNumber;

          if (length != 0)
          {
            for (let i = 0; i < Number(cell.value); i++)
            {
              let div = document.createElement('div'),
                  input = document.createElement('input'),
                  span = document.createElement('span'),
                  label = cell.dataset.input;

              div.setAttribute('class','grid-square');
              input.setAttribute('type','text');
              input.setAttribute('value', label);

              input.setAttribute('name', `product[similarProduct][sort][${grid.childElementCount}]`);
              input.setAttribute('hidden','');

              span.innerHTML = label.charAt(0).toUpperCase() + label.slice(1);

              grid.appendChild(div);
              div.appendChild(input);
              div.appendChild(span);
            }
          }
        });
      }

      thumbnails.forEach((thumbnail, count) =>
      {
        let div = document.createElement('div'),
            input = document.createElement('input'),
            span = document.createElement('span'),
            colorId = thumbnail.dataset.colorId,
            color = thumbnail.dataset.color;

        div.setAttribute('class','grid-square');
        div.style.backgroundImage = thumbnail.style.backgroundImage;
        input.setAttribute('type','text');
        input.setAttribute('value', colorId);
        input.setAttribute('name', `product[similarProduct][sort][${grid.childElementCount}]`);
        input.setAttribute('hidden','');

        span.innerHTML = color;
        span.setAttribute('class','text-center');

        grid.appendChild(div);
        div.appendChild(input);
        div.appendChild(span);

        sliderCount();
      });
    }
  });

  document.addEventListener('click', e =>
  {
    if(e.target.dataset.refreshSimilar == '')
    {
      let btn = e.target,
      parent = btn.closest('[data-product]'),
      thumbnails =  parent.querySelector('[data-thumbnail]')
                          .querySelectorAll('[data-color-id]'),
      grid = document.getElementById('gridProduct'),
      similarProduct = document.getElementById('product_form_similarProduct'),
      qtySet = similarProduct.querySelectorAll('input[type="number"]'),
      max = document.getElementById('product_form_similarProduct_qtySlider');

      thumbnails.forEach((thumbnail, count) =>
      {
        let div = document.createElement('div'),
            input = document.createElement('input'),
            span = document.createElement('span'),
            colorId = thumbnail.dataset.colorId,
            color = thumbnail.dataset.color;

        div.setAttribute('class','grid-square');
        div.style.backgroundImage = thumbnail.style.backgroundImage;
        input.setAttribute('type','text');
        input.setAttribute('value', colorId);
        input.setAttribute('name', `product[similarProduct][sort][${grid.childElementCount}]`);
        input.setAttribute('hidden','');

        span.innerHTML = color;
        span.setAttribute('class','text-center');

        grid.appendChild(div);
        div.appendChild(input);
        div.appendChild(span);

        let sliderCount = document.getElementById('slider-count');
        sliderCount.innerHTML = grid.children.length;
      });
    }
  });

  document.addEventListener('click', e =>
  {
    if(e.target.dataset.removeProduct == '')
    {
      let btn = e.target,
          productList =  btn.closest('div.product-list');

      productList.remove();

      if (products.childElementCount > 0)
      {
        products.children.forEach((product, i) =>
        {
            let label = product.querySelector('label'),
                input = product.querySelector('input');

            label.attributes.for.value = label.attributes.for.value.replace(/\d/gi, i+1);
            label.innerText = 'Product ' + (i+1);

            input.attributes.id.value = input.attributes.id.value.replace(/\d/gi, i+1);
            input.attributes.name.value = input.attributes.name.value.replace(/\d/gi, i+1);

            if (input.attributes.value)
              input.attributes.value.value = input.attributes.value.value.replace(/\d/gi, i+1);
        });
      }
    }

    if(e.target.dataset.removeRemoveSimilarItem == '')
    {
      let btn = e.target,
          cell =  btn.closest('div.grid-square');
      cell.remove();
    }
  });
}
