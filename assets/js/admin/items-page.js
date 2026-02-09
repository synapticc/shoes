// assets/js/admin/items-page.js
/*
  Page:  All admin pages diplaying tables.
  Route: /admin/*
*/
if (document.getElementById('items_page_form') !== null)
{
    let form = document.getElementById('items_page_form'),
        select = form.querySelector('select[name="items_page"]');

    /* Set the number of items to be displayed per page.
       Submit automatically on selecting a number. */
    select.addEventListener('change', (e) => form.submit());
}
