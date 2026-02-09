// assets/js/admin/product/draggable.js

import Sortable from 'sortablejs';
// import { isEmpty as empty }  from '@zerodep/is-empty';
import empty from 'is-blank';
import {addGrid} from './add-grid.js';
import {sliderCount} from './slider-count.js';

if (document.getElementById('gridProduct') !== null)
{
  let grid = document.getElementById('gridProduct'),
      similarProduct = document.getElementById('product_form_similarProduct'),
      qtySet = document.querySelectorAll('input[similar-input=""]'),
      reset = document.getElementById('product_form_resetSlider'),
      sorted, quantity, sum = 0;

      sorted = new Sortable( grid,
      {
      	animation: 150,
      	ghostClass: 'blue-background-class',
        removeOnSpill: true,

        // Element dragging ended
      	onEnd: function (e)
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

          // Mirror grid count to the corresponding input.
          matchInput();
          // Reset slider count.
          sliderCount();
          },
        onRemove: function (e)
        {
	      }
      });

  function matchInput()
  {
    let countArray = [];
    grid.children.forEach((cell, count) =>
    {
      let input = cell.querySelector('input');
      input.setAttribute('name', `product[similarProduct][sort][${count}]`);
      countArray.push(input.value);
    });

    const count = {};
    for (let a = 0; a < countArray.length; a++)
    {
      const b = countArray[a];
      if (count[b]) {
      count[b] += 1;
      } else {
      count[b] = 1;
      }
    }

    for (let i = 0; i < countArray.length; i++)
    {
      // Check if the input is not a number.
      if(Number.isNaN(Number(countArray[i])))
      {
        let target  = document.querySelector(`[data-input=${countArray[i]}]`);
        if (!empty(target) && !empty(countArray[i]))
          target.value = count[countArray[i]];
      }
    }

    qtySet.forEach((qty, i) =>
    {
      if((qty.value == 1) && empty(count[qty.dataset.input]))
        qty.value = 0;
    });
  }


  qtySet.forEach((qty, i) =>
  {
    qty.addEventListener('input', e =>
    {
      let gridInputSet = grid.querySelectorAll(`input[value="${qty.dataset.input}"]`);

      /* Add new element to Grid */
      if (qty.value > gridInputSet.length)
      {
        let div = document.createElement('div'),
            input = document.createElement('input'),
            span = document.createElement('span'),
            label = qty.dataset.input;

        div.setAttribute('class','grid-square');
        input.setAttribute('type','text');
        input.setAttribute('value', label);
        input.setAttribute('name', `product[similarProduct][sort][${grid.childElementCount}]`);
        input.setAttribute('hidden','');

        /*
          Capitalize every word
          - ^   matches the beginning of the string.
          - \w  matches any word character.
          - {1} takes only the first character.
          - ^\w{1} matches the first letter of the word.
          - |   works like the boolean OR.
             It matches the expression after and before the - |.
          - \s+ matches any amount of whitespace between the words
            (for example spaces, tabs, or line breaks).
        */
        span.innerHTML = label.replace(/(^\w{1})/g, letter => letter.toUpperCase());

        grid.appendChild(div);
        div.appendChild(input);
        div.appendChild(span);
      }

      /* Remove surplus element from the Grid. */
      gridInputSet.forEach((gridInput, count) =>
      {
        if ((count+1) > qty.value)
          gridInput.closest('.grid-square').remove();
      });

      // Reset slider count.
      sliderCount();
    });
  });

  reset.addEventListener('click', e =>
  {
    grid.innerHTML = '';
    // sum = 0;

    qtySet.forEach((cell, count) =>
    {
      let length = 0;
      if (cell.dataset.hasOwnProperty('select'))
        length = document.getElementById(cell.dataset.select).selectedOptions.length;
      else if (!cell.dataset.hasOwnProperty('select'))
        length = cell.valueAsNumber;

      if (length != 0)
      {
        for (let i = 0; i < Number(cell.value); i++)
        {
          let div = document.createElement('div'),
              input = document.createElement('input'),
              span = document.createElement('span'),
              label = cell.dataset.input;

          /*
            <div class="grid-square">
              <input
                type="number"
                name="product[similarProduct][sort][0]"
                value="brand"
                hidden="">
              <span>Brand 1</span>count
            </div>
          */
          div.setAttribute('class','grid-square');
          input.setAttribute('type','text');
          input.setAttribute('value', label);
          input.setAttribute('name', `product[similarProduct][sort][${grid.childElementCount}]`);
          input.setAttribute('hidden','');

          span.innerHTML = label.charAt(0).toUpperCase() + label.slice(1);

          grid.appendChild(div);
          div.appendChild(input);
          div.appendChild(span);
        }
      }
    });

    let products = document.getElementById('product_form_similarProduct_otherProducts'),
    thumbnails =  products.querySelectorAll('.thumbnail');

    thumbnails.forEach((thumbnail, count) =>
    {
        let div = document.createElement('div'),
            input = document.createElement('input'),
            span = document.createElement('span'),
            colorId = thumbnail.dataset.colorId,
            label = thumbnail.dataset.label;

        div.setAttribute('class','grid-square');
        div.style.backgroundImage = thumbnail.style.backgroundImage;
        input.setAttribute('type','text');
        input.setAttribute('value', colorId);
        input.setAttribute('name', `product[similarProduct][sort][${grid.childElementCount}]`);
        input.setAttribute('hidden','');

        span.innerHTML = label;
        span.setAttribute('class','text-center');

        grid.appendChild(div);
        div.appendChild(input);
        div.appendChild(span);
    });
  });
}

if (document.getElementById('gridColor') !== null)
{
  let gridColors = document.querySelectorAll('#gridColor');
  gridColors.forEach((grid, i) =>
  {
    if (grid.childElementCount > 0)
    {
      let  main = grid.closest('section[similar-parent]'),
           id = main.id.match(/\d/g)[0],
           sortable = new Sortable(grid,
           {
             group: "name",  // or { name: "...", pull: [true, false, 'clone', array], put: [true, false, array] }
             sort: true,  // sorting inside list
             disabled: false, // Disables the sortable if set to true.
             animation: 150,  // ms, animation speed moving items when sorting, `0` — without animation
             draggable: ".grid-square",  // Specifies which items inside the element should be draggable
             dataIdAttr: 'data-id', // HTML attribute that is used by the `toArray()` method
             direction: 'horizontal', // Direction of Sortable (will be detected automatically if not given)
             // Element dragging ended
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
           });

    }
  });
}


document.addEventListener('input', e =>
{
  if (e.target.dataset.colorQty == '')
  {
    let input = e.target;
    input.setAttribute('value', input.valueAsNumber);

    if (input.value == "")
      input.valueAsNumber = 1;

    if (input.valueAsNumber < 1)
      input.valueAsNumber = 1;

    addGrid(e.target);
  }
});





/* Rearrange product features vertically. */
if (document.getElementById('product_form_features') !== null)
{
  let features = document.getElementById('product_form_features'),
      handle = new Sortable(product_form_features, {
      handle: '.handle', // handle's class
      animation: 150,
    	group: "name",  // or { name: "...", pull: [true, false, 'clone', array], put: [true, false, array] }
    	sort: true,  // sorting inside list
    	delay: 0, // time in milliseconds to define when the sorting should start
    	delayOnTouchOnly: false, // only delay if user is using touch
    	touchStartThreshold: 0, // px, how many pixels the point should move before cancelling a delayed drag event
    	disabled: false, // Disables the sortable if set to true.
    	store: null,  // @see Store
    	animation: 150,  // ms, animation speed moving items when sorting, `0` — without animation
    	easing: "cubic-bezier(1, 0, 0, 1)", // Easing for animation. Defaults to null. See https://easings.net/ for examples.
    	handle: ".handle",  // Drag handle selector within list items
    	filter: ".ignore-elements",  // Selectors that do not lead to dragging (String or Function)
    	preventOnFilter: true, // Call `event.preventDefault()` when triggered `filter`
    	draggable: ".feature-list",  // Specifies which items inside the element should be draggable

    	dataIdAttr: 'data-id', // HTML attribute that is used by the `toArray()` method

    	ghostClass: "sortable-ghost",  // Class name for the drop placeholder
    	chosenClass: "sortable-chosen",  // Class name for the chosen item
    	dragClass: "sortable-drag",  // Class name for the dragging item

    	swapThreshold: 1, // Threshold of the swap zone
    	invertSwap: false, // Will always use inverted swap zone if set to true
    	invertedSwapThreshold: 1, // Threshold of the inverted swap zone (will be set to swapThreshold value by default)
    	direction: 'horizontal', // Direction of Sortable (will be detected automatically if not given)

    	forceFallback: false,  // ignore the HTML5 DnD behaviour and force the fallback to kick in

    	fallbackClass: "sortable-fallback",  // Class name for the cloned DOM Element when using forceFallback
    	fallbackOnBody: false,  // Appends the cloned DOM Element into the Document's Body
    	fallbackTolerance: 0, // Specify in pixels how far the mouse should move before it's considered as a drag.

    	dragoverBubble: false,
    	removeCloneOnHide: true, // Remove the clone element when it is not showing, rather than just hiding it
    	emptyInsertThreshold: 5,
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

        features.children.forEach((cell, count) =>
        {
          let input = cell.querySelector('input'),
              label = cell.querySelector('label');

          input.setAttribute('name', `product_form[features][${count+1}]`);
          label.innerHTML = `Feature ${count+1}`;
        });
      },
  });
}
