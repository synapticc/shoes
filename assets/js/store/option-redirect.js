// assets/js/store/option-redirect.js
/*
  Page:  Store Details
  Route: store_details

  > Automatically redirect to new product detail page
    once a new size is selected.
*/
if (document.querySelector('#size-picker') !== null)
{
  var sizePicker = document.querySelector('#size-picker');

  sizePicker.addEventListener('change', () =>
  {
    if (sizePicker.options[sizePicker.selectedIndex])
    {
      if (sizePicker.options[sizePicker.selectedIndex].dataset.redirect)
      {
        window.document.location.href  = sizePicker.options[sizePicker.selectedIndex].dataset.redirect;
      }
    }
  });
}
