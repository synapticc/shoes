// assets/js/admin/increase-btn.js

import {addGrid} from './product/add-grid.js';

/* Clear search
 */
document.addEventListener('click', e =>
{
  if(e.target.dataset.increaseBtn == '')
  {
      let increaseBtn = e.target,
          input =  document.getElementById(increaseBtn.dataset.input);

      if (input.value == "")
        input.valueAsNumber = 1;

      // minimum is 1
      if (input.valueAsNumber < 1)
        input.valueAsNumber = 1;

      if(input.valueAsNumber < input.max)
      {
        let newValue = parseInt(input.valueAsNumber + 1);
        input.value = newValue;
        input.setAttribute('value', newValue);
      }

      addGrid(input);
  }

  if(e.target.dataset.decreaseBtn == '')
  {
    let decreaseBtn = e.target,
        input =  document.getElementById(decreaseBtn.dataset.input);

    if (input.value == "")
      input.valueAsNumber = 1;

    // minimum is 1
    if (input.valueAsNumber < 1)
      input.valueAsNumber = 1;

    if(input.valueAsNumber > 1)
      input.value--;

    addGrid(input);
  }
});



// if (document.querySelector('form[name="add_to_cart_form"]') !== null)
// {
//     let cartQtyInput =  document.getElementById('add_to_cart_form_quantity'),
//         maxInputQty = cartQtyInput.max;
//
//     const setInput = e => {
//       e.preventDefault();
//       e.stopPropagation();
//
//       if (cartQtyInput.valueAsNumber > maxInputQty )
//         cartQtyInput.valueAsNumber = maxInputQty;
//
//       if (cartQtyInput.value == "")
//         cartQtyInput.valueAsNumber = 1;
//
//       if (cartQtyInput.valueAsNumber < 1)
//       {
//         cartQtyInput.valueAsNumber = 1;
//       } // minimum is 1
//     }
//
//     cartQtyInput.addEventListener('input', setInput );
//     cartQtyInput.addEventListener('change', setInput);
// }
