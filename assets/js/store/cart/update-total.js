// assets/js/store/cart/update-total.js

import separateComma from '../../separate-comma.js';

export default function updateTotal(item)
{
  let table = document.getElementById(item.dataset.table),
  cart = item.dataset.cart,
  totalAmt = document.getElementById(table.dataset.total);

  const url = `update-total/${cart}`;

  fetch(url).
  then(response => response.json()).
  then(total =>
    {
      totalAmt.innerHTML  = `Rs ${separateComma(total.toFixed(2))}`;
    }
  );
}
