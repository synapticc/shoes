// assets/js/store/sort-by.js
/*
  Page:  Store Listing
  Route: /store

  Since the size and color choices of the sidebar
  are not in checkbox or radio box format,
  make each size and color act like a checkbox.
    > On choosing a color, add 'checked' attribute
      to its correspoding input.
    > Add active styling.
    > Submit the filter form.
*/
if (document.querySelector('#sort-by') !== null)
{
  let sortBy = document.querySelector('#sort-by'),
      filter = document.getElementById('filter'),
      order = filter.querySelector('#filter-order');

  sortBy.addEventListener('change', (e) =>
  {
    let sortValue = sortBy.options[sortBy.selectedIndex].value;
    order.setAttribute('value',sortValue);
    order.value  =  sortValue;
    filter.submit();
  });

  /* Gets the value associated with the key
    window.location.search => ?price[order]=nameAsc&size[80]=80&price[min]=500&price[max]=15000
  */
  // Address of the current window
  let address = window.location.search;

  // Returns a URLSearchParams object instance
  let parameterList = new URLSearchParams(address),
      sizes = [],
      colors = [],
      colorExclude = [];

  parameterList.forEach(function(value, key)
  {
    if (key == 'size[]')
      sizes = sizes.concat(value);
    if (key == 'color[]')
      colors = colors.concat(value);
    if (key == 'colorExclude[]')
      colorExclude = colorExclude.concat(value);
  });

  /* Filter sidebar : Size */
  let inputAll = document.querySelectorAll('input'),
      tableSize = document.querySelector('.ps-table--size'),
      tableSizeAll = document.querySelectorAll('.ps-table--size td');

  tableSizeAll.forEach((cell, i) =>
  {
    let inputCell = cell.querySelector('input'),
        sizeValue = cell.querySelector('input').value;

    if (sizes.includes(sizeValue) )
    {
        inputCell.setAttribute('checked','checked');
        cell.setAttribute('class','active');
    }

    cell.addEventListener('click', () =>
    {

      if (inputCell.getAttribute('checked'))
      {
        inputCell.removeAttribute('checked');
        cell.removeAttribute('class','');

      } else if( inputCell.getAttribute('checked') == null)
      {
        inputCell.setAttribute('checked','checked');
        cell.setAttribute('class','active');
      }

      filter.submit()

    });
  });


  /* Filter sidebar : Color
    > Disable all colors that have been excluded
      to prevent them from being selected again.
  */
  var listColor = document.querySelector('#list-color'),
      listColorAll = document.querySelectorAll('#list-color li');

  listColorAll.forEach((list, i) =>
  {
      let inputList = list.querySelector('input'),
          colorValue = list.querySelector('input').value;

      if (colors.includes(colorValue) )
      {
          inputList.setAttribute('checked','checked');
          list.setAttribute('class','active');
      }

      if (colorExclude.includes(colorValue) )
      {
        colorExlcudeHover = list.nextElementSibling;
        list.style.cursor = 'not-allowed';
        colorExlcudeHover.className = 'top-disabled';
        inputList.setAttribute('disabled','');
      }

        list.addEventListener('click', () =>
        {
          if (!inputList.hasAttribute('disabled'))
          {
            if (inputList.getAttribute('checked'))
            {
              inputList.removeAttribute('checked');
              list.removeAttribute('class','');
            }
            else if( inputList.getAttribute('checked') == null)
            {
              inputList.setAttribute('checked','checked');
              list.setAttribute('class','active');
            }

            filter.submit();
          }
        });
  });

}
