// assets/js/admin/product/add-grid.js

import Sortable from 'sortablejs';

function addGrid(input)
{
  let main = input.closest('section[similar-parent]'),
      id = main.id.match(/\d/g)[0],
      qtySet = main.querySelectorAll('input[data-color-qty]'),
      grid = main.querySelector('#gridColor');

  const options =
  {
    animation: 150,
    ghostClass: 'blue-background-class',
    removeOnSpill: true,

    // Element dragging ended
    onEnd: function (/**Event*/e)
    {
      let itemEl = e.item;  // dragged HTMLElement
      e.to;    // target list
      e.from;  // previous list
      e.oldIndex;  // element's old index within old parent
      e.newIndex;  // element's new index within new parent
      e.oldDraggableIndex; // element's old index within old parent, only counting draggable elements
      e.newDraggableIndex; // element's new index within new parent, only counting draggable elements
      e.clone // the clone element
      e.pullMode;  // when item is in another sortable: `"clone"` if cloning, `true` if moving

      let countArray = [];
      grid.children.forEach((cell, count) =>
      {
        let input = cell.querySelector('input');
        input.setAttribute('name', `product_form[colors][${id}][similarProductColor][sort][${count}]`);
        countArray.push(input.value);
      });
      },
    onRemove: function (e)
    {
    }
  };

  let sorted = new Sortable(grid,  options);

  qtySet.forEach((qty, i) =>
  {
    let parent = qty.closest('div[data-color-patch-parent]'),
        select = parent.querySelector('select'),
        color = select.options[select.selectedIndex],
        label = color.value,
        txt = color.innerHTML;

    if (label == '') {return false;}

    for (let i = 0; i < qty.valueAsNumber; i++)
    {
      /* Add new element to Grid */
      if (qty.valueAsNumber > grid.querySelectorAll(`input[value="${label}"]`).length)
      {
        let div = document.createElement('div'),
            input = document.createElement('input'),
            span = document.createElement('span');

          div.setAttribute('class','grid-square');
          div.style.backgroundImage = "url(" + document.location.origin + "/build/images/" + label + "_sample.png)";
          div.style.backgroundSize = '70%';
          div.style.backgroundPosition = '50% 68%';
          input.setAttribute('type','text');
          input.setAttribute('value', label);
          input.setAttribute('name', `product_form[colors][${id}][similarProductColor][sort][${grid.childElementCount}]`);
          input.setAttribute('hidden','');
          span.innerHTML = txt;

          grid.appendChild(div);
          div.appendChild(input);
          div.appendChild(span);
      }
    }

    let gridInputSet = grid.querySelectorAll(`input[value="${label}"]`);

    /* Remove surplus element to Grid */
    gridInputSet.forEach((gridInput, count) =>
    {
      if ((count+1) > qty.value)
        gridInput.closest('.grid-square').remove();
    });
  });
}

export {addGrid};
