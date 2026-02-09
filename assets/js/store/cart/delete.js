// assets/js/store/cart/delete.js


const flashOptions =
{
  progress: false,
  interactive: true,
  timeout: 800,
  appear_delay: 10,
  container: '.flash-container',
  theme: 'default',
  classes: {
      container: 'flash-container',
      flash: 'flash-message',
      visible: 'is-visible',
      progress: 'flash-progress',
      progress_hidden: 'is-hidden'
  }};


/* AJAX: Delete the entire cart by the passing the cart ID as argument.
  Route used : delete-cart/cart_id
  - On successful deletion, flash the 'Cart removed' message.
  - Change the top cart content.
  */
function deleteCart(cart)
{
  let url = `delete-cart/${cart.dataset.cart}`,
  topCart = document.querySelector('#cart-listing');

  fetch(url).
  then(response =>
    {
      if(response.status == 200)
        window.FlashMessage.success('Cart removed', flashOptions);

      return response.json();
    }).
  then(data => topCart.outerHTML = data);
}

/* AJAX: Empty the cart by the passing the cart ID as argument.
  Route used : clear-cart/cart_id
  - On successful emptying, flash the 'Cart cleared' message.
  - Change the top cart content.
  */
function clearCart(cart)
{
  let url = `clear-cart/${cart.dataset.cart}`,
  topCart = document.querySelector('#cart-listing');

  fetch(url).
  then(response =>
    {
      if(response.status == 200)
        window.FlashMessage.success('Cart cleared', flashOptions);

      return response.json();
    }).
  then(data => topCart.outerHTML = data);
}

document.addEventListener(
  'click',
  (e) =>
{
  if (e.target.id == 'delete-cart')
  {
    let cart = e.target,
    cartTable = document.getElementById(cart.dataset.cartTable);

    deleteCart(cart);
    cartTable.remove();
  }

  if (e.target.id == 'clear-cart')
  {
    let cart = e.target,
    cartItems = document.getElementById(cart.dataset.cartItems);

    clearCart(cart);
    cartItems.innerHTML='';
  }

});
