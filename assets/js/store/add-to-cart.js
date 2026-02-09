// assets/js/store/add-to-cart.js

/*
  Page:  The cart input form || Product Detail
  Route: /store/{brand}/{name}/{id}
  Description:
    > Set the minimum cart value to 1.
    > Prevent negative integer from being inputted.
    > Convert any non-integer to integer.
    > Replace empty entry with 1.

*/
if (document.querySelector('form[name="add_to_cart_form"]') !== null)
{
    let cartQtyInput =  document.getElementById('add_to_cart_form_quantity'),
        maxInputQty = cartQtyInput.max;

    const setInput = e => {
      e.preventDefault();
      e.stopPropagation();

      if (cartQtyInput.valueAsNumber > maxInputQty )
        cartQtyInput.valueAsNumber = maxInputQty;

      if (cartQtyInput.value == "")
        cartQtyInput.valueAsNumber = 1;

      if (cartQtyInput.valueAsNumber < 1)
      {
        cartQtyInput.valueAsNumber = 1;
      } // minimum is 1
    }

    cartQtyInput.addEventListener('input', setInput );
    cartQtyInput.addEventListener('change', setInput);
}
