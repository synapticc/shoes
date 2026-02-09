// assets/js/admin/product/remove-grid.js

import Sortable from 'sortablejs';

function removeGrid(color, selectId)
{
  let select = document.getElementById(selectId),
      main = select.closest('section[similar-parent]'),
      id = main.id.match(/\d/g)[0],
      qtySet = main.querySelectorAll('input[data-color-qty]'),
      grid = main.querySelector('#gridColor'),
      gridInputSet = grid.querySelectorAll(`input[value="${color}"]`);

  /* Remove surplus element to Grid */
  gridInputSet.forEach((gridInput, count) =>
  {
    // if ((count+1) > qty.value)
      gridInput.closest('.grid-square').remove();
  });

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
}

export {removeGrid};
