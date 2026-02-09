// assets/js/store/cart/undo.js

import updateTotal from './update-total.js';
import updateTopCart from './update-top.js';
import paginate from './paginate.js';
import separateComma from '../../separate-comma.js';

/*
  Page:  Cart
  Route: cart

  > Retrieve cart item using 'Undo' button
  > Remove cart item using 'Cancel' button
*/

/* Delay the removal of cart item to allow HTMX to be fired.
   AJAX (HTMX) is used to remove the cart item instantly.
   HTMX is given time to fire, then the item tag is removed.*/
function removeParent(parent, delay)
{
  setTimeout(function()
  {
    if (parent != null)
      parent.remove();
  },
  delay // delay
  );
}


function removeItem(item)
{
  let input = document.getElementById(item.dataset.quantity),
  quantity = parseInt(input.value),
  undo = document.getElementById(item.dataset.undo),
  cartRow = item.closest('tr')
  ;

  const url = `${item.dataset.url}/${quantity}`;

  fetch(url).
  then(response =>
    {
      // Check if the request was successful
      if (response.ok)
      { // Flash message
        window.FlashMessage.error('Item removed', flashOptions);
        // Remove row
        cartRow.remove();
        // Readjust total sum
        updateTotal(item);
      }
      // Parse the response as JSON
      return response.json();}).
  then(data =>
    {
      undo.insertAdjacentHTML("afterbegin", data.item);

      let table = document.getElementById(item.dataset.table),
      itemsCount = document.getElementById(item.dataset.itemsCount);

      paginate(table);

      itemsCount.innerHTML = `Products (${data.count} items)`;

    });

  updateTopCart();
}

function undoItem(item)
{
  const url = item.dataset.url;

  fetch(url).
  then(response =>
    {
      // Check if the request was successful
      if (response.ok)
        window.FlashMessage.success('Item retrieved', flashOptions);

      // Parse the response as JSON
      return response.json(); }).
  then(data =>
    {
      let table = document.getElementById(item.dataset.table),
      itemsCount = document.getElementById(item.dataset.itemsCount),
      topCart = document.querySelector('#cart-listing'),
      totalAmt = document.getElementById(table.dataset.total);

      table.querySelector('tbody').insertAdjacentHTML("afterbegin", data.item);

      paginate(table);

      itemsCount.innerHTML = `Products (${data.count} items)`;

      topCart.outerHTML = data.top;

      totalAmt.innerHTML = `Rs ${separateComma(Number(data.total).toFixed(2))}`;
    });
}

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

document.addEventListener(
  'click',
  (e) =>
{
  /* When 'Undo' is clicked, the cart item is retrieved
     and re-inserted at the beginning (top) of the table.
  */
  if (e.target.id == 'cart_item_retrieve')
  {
    let undoBtn =  e.target,
        undoMsg = undoBtn.closest('p');

    // Retrieve from database
    undoItem(undoBtn);

    // Remove undo item message
    undoMsg.remove();

  }

  /* When 'Remove' is click, the cart item is removed from database. */
  if (e.target.id == 'cart_item_remove')
  {
    let itemRemove =  e.target;
    removeItem(itemRemove);
  }

  /* When 'Undo' is cancelled, the dialog box is removed as well. */
  if (e.target.id == 'cart_item_retrieve_cancel')
  {
    let cancelBtn =  e.target,
        parent = cancelBtn.closest('p');

    parent.remove();
  }
});
