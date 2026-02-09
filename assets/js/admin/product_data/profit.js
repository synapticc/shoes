// assets/js/admin/product_data/profit.js

import { isEmpty as empty }  from '@zerodep/is-empty';

if (document.querySelector('input[name="product_data_form[profit]"]') !== null)
{
  let profit = document.querySelector('input[name="product_data_form[profit]"]'),
      costPrice = document.querySelector('input[name="product_data_form[costPrice]"]'),
      sellingPrice = document.querySelector('input[name="product_data_form[sellingPrice]"]');

  profit.addEventListener('input', () =>
  {
    let margin = parseFloat(profit.value).toFixed(2);

    if (!empty(costPrice.value))
      sellingPrice.value =  Number.parseFloat( costPrice.value * (1+(margin/100)) ).toFixed(2);

  });

  costPrice.addEventListener('input', () =>
  {
    let margin = parseFloat(profit.value).toFixed(2);
    if (!empty(costPrice.value) && !empty(profit.value))
      sellingPrice.value =  Number.parseFloat( costPrice.value * (1+(margin/100)) ).toFixed(2);

  });

  sellingPrice.addEventListener('input', () =>
  {
    let margin = Number.parseFloat(((sellingPrice.value-costPrice.value)/costPrice.value)*100 ).toFixed(2);
    profit.value =  margin;
  });

}
