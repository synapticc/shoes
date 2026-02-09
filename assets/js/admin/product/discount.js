// assets/js/admin/product/discount.js

import empty from 'is-blank';

/* Check if the discount start date and end date are not empty
   if a discount has been set.
   The current start date and end date are hidden and 'required'
   validation cannot be applied directly.
*/
if (document.getElementById('product_form_discount_discount') !== null)
{
  let productForm = document.querySelector('form[name="product_form"]');
  productForm.addEventListener('submit', e =>
  {
    let discount = document.getElementById('product_form_discount_discount');
    if (!empty(discount.value))
    {
      let startDate = document.getElementById('product_form_discount_startDate'),
          endDate = document.getElementById('product_form_discount_endDate');

      if (empty(startDate.value) && empty(endDate.value))
      {   e.preventDefault();}
    }
  });
}
