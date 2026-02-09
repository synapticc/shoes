// assets/js/store/cart/paginate.js

import paginator from '../../../plugins/store/paginate/paginator.js';

export default function paginate(table)
{
  if (table.nextElementSibling)
    if(table.nextElementSibling.classList.contains('box'))
      table.nextElementSibling.remove();

  let rows = table.querySelector('tbody').querySelectorAll('tr');
  rows.forEach((row, i) =>
    {
      if (row.style.display == 'none')
        row.style.display =  'table-row';
    });

  let box = paginator({
      table: table,
      box_mode: "list",
      rows_per_page: 5,
      page_options: false,
      disable: false,
    });

  box.className = "box d-flex justify-content-center";
  box.setAttribute('id','pagination');
  table.parentNode.appendChild(box);
}
