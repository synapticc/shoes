// assets/js/store/cart/cart.js

import htmx from "htmx.org";
import separateComma from '../../separate-comma.js';
import updateTotal from './update-total.js';
import updateTopCart from './update-top.js';
import empty from 'is-blank';

if (document.getElementById('cart-table'))
{
    let cartQtyAll =  document.querySelectorAll('input[cart-qty]'),
        decreaseQtyAll = document.querySelectorAll('#decreaseQty'),
        increaseQtyAll = document.querySelectorAll('#increaseQty'),
        itemSumAllOnLoad = document.querySelectorAll('td#items-sum'),
        cartFormAll = document.querySelectorAll('#cart-form'),
        cancelBtnAll = document.querySelectorAll('#cancel_cart_changes'),
        // cartStatusAll =  document.querySelectorAll('input[data-status]'),
        // itemRemoveAll = document.querySelectorAll('#cart_item_remove'),
        // itemRetrieveAll = document.querySelectorAll('#cart_item_retrieve'),
        itemsChanged = [];

    const flashOptions =
    {
      progress: false,
      interactive: true,
      timeout: 800,
      appear_delay: 10,
      container: '.flash-container',
      theme: 'default',
      classes: {
          container: 'flash-container',
          flash: 'flash-message',
          visible: 'is-visible',
          progress: 'flash-progress',
          progress_hidden: 'is-hidden'
      }};

    function disableBtn(input)
    {
      let cartRow = document.getElementById(input.dataset.cartRow),
      decreaseQty = cartRow.querySelector('#decreaseQty'),
      increaseQty = cartRow.querySelector('#increaseQty'),
      maxQty = input.max;

      if (input.valueAsNumber == 1)
        if (!decreaseQty.classList.contains('disabled-cart-btn'))
          decreaseQty.classList.add('disabled-cart-btn');

      if (input.valueAsNumber > 1)
        if (decreaseQty.classList.contains('disabled-cart-btn'))
          decreaseQty.classList.remove('disabled-cart-btn');


      if (input.valueAsNumber == maxQty)
        if (!increaseQty.classList.contains('disabled-cart-btn'))
          increaseQty.classList.add('disabled-cart-btn');

      if (input.valueAsNumber < maxQty)
        if (increaseQty.classList.contains('disabled-cart-btn'))
          increaseQty.classList.remove('disabled-cart-btn');

    }

    function saveQty(input, newQty)
    {
      if(typeof(newQty) !== 'number' || empty(newQty) || newQty < 0)
        return false;

      const url =  `${input.dataset.url}?quantity=${newQty}`;

      fetch(url).
      then(response =>
        {
          // Check if the request was successful
          if (response.ok)
           // Flash message
            window.FlashMessage.success('Quantity saved', flashOptions);

          // Parse the response as JSON
          return response.json();}).
      then(data =>
        {
          let topCart = document.querySelector('#cart-listing'),
          total = document.getElementById(input.dataset.total);

          topCart.outerHTML = data.top;

          total.innerHTML = `Rs ${separateComma(Number(data.total).toFixed(2))}`;

        });
    }

    // Twig extension cannot both add comma and convert
    // to float format when multiplying.
    // itemSumAllOnLoad.forEach((itemSum, i) =>
    // {
    //     window.addEventListener('load',
    //       () =>
    //         {
    //           let itemSumValue =  parseFloat(itemSum.dataset.itemSum).toFixed(2);
    //           // itemSum.innerHTML  = `Rs ${separateComma(itemSumValue)}`;
    //         });
    // });

    /*
      Event for #qty field
        - Prevent
          1) zero,
          2) negative,
          3) decimal and
          4)non-numeric values,
        - Set 1 as minimum value (mandatory)
    */
    document.addEventListener(
      'change',
      (e) =>
    {
      if (e.target.dataset.cartQty=='')
      {
        let input= e.target,
        maxQty = input.max,
        cartRow = document.getElementById(input.dataset.cartRow),
        checkout = document.getElementById(input.dataset.checkout),
        itemSum = cartRow.querySelector('td#items-sum'),
        itemPrice = cartRow.querySelector('span#item-price'),
        newQty = parseInt(input.valueAsNumber),
        itemSumValue
        ;

        if (newQty > maxQty )
          input.value = maxQty;

        if (input.value < 1 || input.value < 0 || empty(input.value))
          input.value = 1;  // minimum is 1

        itemSumValue = parseFloat(itemPrice.dataset.itemPrice
                       * input.valueAsNumber).toFixed(2);

        itemSum.setAttribute('data-item-sum', itemSumValue);
        itemSum.innerHTML  = `Rs ${separateComma(itemSumValue)}`;

        // Save to database after a 100ms delay
        // setTimeout(() => saveQty(input, newQty), 100 /*delay*/);
console.log(empty(input.value) && empty(newQty),empty(input.value) ,empty(newQty), input.value,newQty);
        if (empty(input.value) && empty(newQty))
        {
          if (checkout.classList.contains('ps-btn'))
            checkout.classList.replace('ps-btn', 'ps-btn-disabled');
        }
        else if(!empty(input.value)  && !empty(newQty))
        {
          if (checkout.classList.contains('ps-btn-disabled'))
            checkout.classList.replace('ps-btn-disabled', 'ps-btn');

          saveQty(input, newQty);
          disableBtn(input);
        }
      }
    });


    document.addEventListener(
      'click',
      (e) =>
    {
      if (e.target.id == 'decreaseQty')
      {
        let decreaseQty =  e.target,
        quantity = document.getElementById(decreaseQty.dataset.target),
        cartRow = document.getElementById(decreaseQty.dataset.cartRow),
        itemSum = cartRow.querySelector('td#items-sum'),
        itemPrice = cartRow.querySelector('span#item-price'),
        originalQty = parseInt(quantity.dataset.originalQty),
        itemSumValue, newQty=1;

        if (quantity.value == "" || quantity.value < 1 || quantity.value < 0)
          quantity.value = 1;  // minimum is 1

        if (parseInt(quantity.value) > 1)
        {
          newQty = parseInt(quantity.value)-1;
          quantity.setAttribute('value', newQty);
          quantity.value = newQty;

          itemSumValue =
            parseFloat((parseInt(itemPrice.dataset.itemPrice) * newQty) ).toFixed(2);
          itemSum.setAttribute('data-item-sum', itemSumValue );
          itemSum.innerHTML  = `Rs ${separateComma(itemSumValue)}`;

        } //decrement for all positive integers

        if (!decreaseQty.classList.contains('disabled-cart-btn'))
          saveQty(quantity, newQty);

        updateTotal(decreaseQty);
        updateTopCart();
        disableBtn(quantity);
      }


      if (e.target.id == 'increaseQty')
      {
        let increaseQty =  e.target,
        quantity = document.getElementById(increaseQty.dataset.target),
        maxQty = parseInt(quantity.max),
        cartRow = document.getElementById(increaseQty.dataset.cartRow),
        itemSum = cartRow.querySelector('td#items-sum'),
        itemPrice = cartRow.querySelector('span#item-price'),
        itemSumValue, newQty=1;

        if (parseInt(quantity.value) < maxQty)
        {
          newQty = parseInt(quantity.value)+1;
          quantity.setAttribute('value', newQty);
          quantity.value = newQty;

          itemSumValue =
            parseFloat(itemPrice.dataset.itemPrice * newQty).
            toFixed(2);
          itemSum.setAttribute('data-item-sum', itemSumValue );
          itemSum.innerHTML  = `Rs ${separateComma(itemSumValue)}`;
        } //increment for all positive integers

        if (parseInt(quantity.value) > maxQty)
          quantity.value = maxQty;

      if (!increaseQty.classList.contains('disabled-cart-btn'))
        saveQty(quantity, newQty);

      updateTotal(increaseQty);
      updateTopCart();
      disableBtn(quantity);
      }
    });

    /* Description:
        > Disable form submision on pressing 'ENTER'.
          This would have activated the button with
          name="order_0[items][0][remove]" which
          deletes the item.
    */
    // cartFormAll.forEach((cartForm) =>
    // {
    //   cartForm.addEventListener('keypress',
    //     function (e)
    //     {
    //       if (e.keyCode === 13 || e.which === 13)
    //       {
    //         e.preventDefault();
    //         return false;
    //       }
    //     });
    // });

    // Make all the cart status checkboxes act as radio button
    // cartStatusAll.forEach((cartStatus, i) =>
    // {
    //   cartStatus.addEventListener('click', e =>
    //   {
    //     if (cartStatus.getAttribute('checked'))
    //       cartStatus.removeAttribute('checked');
    //     else if( cartStatus.getAttribute('checked') == null)
    //       cartStatus.setAttribute('checked','checked');
    //
    //     if (cartStatus.checked == true)
    //     {
    //       for (var cart of cartStatusAll)
    //       {
    //         if (cartStatus != cart)
    //         {
    //           if (cart.checked == true)
    //           {
    //             cart.checked == false;
    //
    //             if (cart.getAttribute('checked'))
    //               cart.removeAttribute('checked');
    //
    //             // Simulate clicking the other toggle button
    //             let label = cart.closest('div').querySelector('label');
    //             label.click();
    //           }
    //         }
    //       }
    //     }
    //
    //     let cartForm =  cartStatus.closest('form');
    //     cartForm.submit()
    //   });
    // });
}




/* Alternate way to calculate the sum total after each changes using
   client-side JS. */
// export default function cartTotal(item)
// {
//   let table = document.getElementById(item.dataset.table),
//   cartItems = table.querySelector('tbody').querySelectorAll('tr'),
//   totalAmt = document.getElementById(table.dataset.total);
//
//   let cartTotal = 0;
//   cartItems.forEach((cartItem, i) =>
//   {
//     let subTotal = parseFloat(cartItem.querySelector('td#items-sum').dataset.itemSum);
//     cartTotal += subTotal;
//   });
//
//   totalAmt.innerHTML  = `Rs ${separateComma(cartTotal.toFixed(2))}`;
// };
