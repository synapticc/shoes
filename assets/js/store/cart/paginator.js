// assets/js/store/cart/paginator.js

import paginator from '../../../plugins/store/paginate/paginator.js';

/*
  Page:  Cart
  Route: cart

  Paginate the cart table using JavaScript instead of PHP.
  Pagination is done on the client-side when all items
  have already been loaded.

  https://github.com/jamesonmccowan/table-paginator

*/
if (document.querySelector('table[data-paginate]') !== null)
{
    var cartTableSet = document.querySelectorAll('table[data-paginate]');

    function toggleVisibility(pagination)
    {
      if (pagination.childElementCount === 5)
      {
        if (!(pagination.classList.contains('hide')))
          pagination.classList.add('hide');
      }
      else if (pagination.childElementCount > 5)
      {
        if (pagination.classList.contains('hide'))
          pagination.classList.remove('hide');
      }
    }

    cartTableSet.forEach((cartTable, i) =>
    {
        let items = Number(cartTable.dataset.items),
            pageOptions = '', display;

        if ( items == 0 || items == ''|| (items > 0 && items < 4) )
          display = true;
        else if(items > 5 )
          display = false;

        let box = paginator({
            table: cartTable,
            box_mode: "list",
            rows_per_page: 5,
            page_options: false,
            disable: display,
          });

        box.className = "box d-flex justify-content-center";
        box.setAttribute('id','pagination');

        let pagination =  box.querySelector('.pagination'),
            selectPagination =  box.querySelector('select');

        if(cartTable.nextSibling)
          cartTable.parentNode.insertBefore(box,cartTable.nextSibling);
        else
          cartTable.parentNode.appendChild(box);
    });

    /* Original instruction

    var box = paginator({
        table: document.getElementById("cart-table").getElementsByTagName("table")[0],
        box_mode: "list",
        rows_per_page: 3
    });

    box.className = "box";
    document.getElementById("cart-table").appendChild(box);

    */
}
