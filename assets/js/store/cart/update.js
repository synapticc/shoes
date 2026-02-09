// assets/js/store/cart/update.js

import htmx from "htmx.org";
import cookies from 'js-cookie';
import empty from 'is-blank';
import paginator from '../../../plugins/store/paginate/paginator.js';
import paginate from './paginate.js';

/* Option 1: Use a cookie to store the latest cart 'update' date.
   It is quicker, though the cookie needs to be refreshed each time.
   Doesn't work in a few cases.

   Option 2: Fetch the latest cart 'update' date, compare it with
   page current latest date and then update or update directly since
   it is fast anyway.
   Works in all cases.
   */
function updateCart()
{
  let route = document.getElementById('body').dataset.route,
  topCart = document.getElementById('cart-listing');

   // Update top cart for all pages
   if (route !== 'cart')
   {
     let url = `/update-cart`;
     fetch(url).
     then(res => res.json()).
     then(data => topCart.outerHTML = data);
   }
   // Update Cart page main table & and top cart
   else
   {
     let urlList = `/update-list`;

     fetch(urlList).
     then(response => response.json()).
     then(data =>
       {
         let cartBody = document.getElementById(data.id),
         table = document.getElementById(cartBody.dataset.table),
         itemsCount = document.getElementById(cartBody.dataset.itemsCount);

         cartBody.outerHTML = data.tbody;

         paginate(table);

         itemsCount.innerHTML = `Products (${data.count} items)`;

         topCart.outerHTML = data.topCart;
      });
   }
}

/* On switching to a tab, (coming either from a previous tab or from another
   browser), update the cart for that page.
 */
document.addEventListener(
  'visibilitychange',
  (e) =>
  {
    if (document.visibilityState == "visible")
      updateCart();
  });

document.addEventListener('mouseenter', updateCart, { once: true });
