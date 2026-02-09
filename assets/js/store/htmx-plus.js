// assets/js/store/htmx-plus.js

import htmx from "htmx.org";
import zoomBoxExecute from '../../plugins/store/zoombox/js/zoombox.min.js';

/* Flashes a side popup message to confirm a change.
   Use the HTMX events.

   Messages:
    > 'New quantity saved': An item is saved to the cart.
    > 'Item retrieved': A cart item is retrieved with 'Undo'.
    > 'item-undo': A cart item is deleted.

*/
document.addEventListener('htmx:afterSwap', (e) =>
{
  if (e.detail.target.id === 'reviews')
  {
    zoomBoxExecute();
  }

  if (e.detail.target.id === 'cart-listing')
  {
    window.FlashMessage.success('Item saved', {
      progress: false, // displays a progress bar at the bottom of the flash message
      interactive: true, // Define flash message actions (pause on mouseover, close on click)
      timeout: 700, // Flash message timeout
      appear_delay: 10, // Delay before flash message appears
      container: '.flash-container', // Flash messages container element selector
      theme: 'default', // CSS theme (availables: default, dark)
      classes: {
          container: 'flash-container', // Custom container css class
          flash: 'flash-message', // Flash message element css class
          visible: 'is-visible', // Flash message element css visible class
          progress: 'flash-progress', // Flash message progress bar element css class
          progress_hidden: 'is-hidden' // Flash message progress bar element hidden css class
      }
    });
  }


  if (e.detail.target.dataset.id === 'tbody-cart')
  {
    htmx.process(e.detail.target);

    window.FlashMessage.info('Item retrieved', {
      progress: false,
      interactive: true,
      timeout: 3000,
      appear_delay: 10,
      container: '.flash-container',
      theme: 'default',
      classes: {
          container: 'flash-container',
          flash: 'flash-message',
          visible: 'is-visible',
          progress: 'flash-progress',
          progress_hidden: 'is-hidden'
      }
    });
  }


  if (e.detail.target.dataset.id === 'item-undo')
  {
    window.FlashMessage.warning('Item deleted', {
      progress: false,
      interactive: true,
      timeout: 3000,
      appear_delay: 10,
      container: '.flash-container',
      theme: 'default',
      classes: {
          container: 'flash-container',
          flash: 'flash-message',
          visible: 'is-visible',
          progress: 'flash-progress',
          progress_hidden: 'is-hidden'
      }
    });
  }
});
