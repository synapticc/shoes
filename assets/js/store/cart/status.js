// assets/js/store/cart/status.js

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

function deactivateCart()
{
  let url = `deactivate-cart`,
  topCart = document.querySelector('#cart-listing');

  fetch(url).
  then(response =>
    {
      if(response.status == 200)
        window.FlashMessage.success('Cart changed', flashOptions);

      return response.json();
    }).
  then(data => topCart.outerHTML = data);
}

document.addEventListener(
  'click',
  (e) =>
{
  if (e.target.dataset.cartStatus == '')
  {
    let cartStatusSet = document.querySelectorAll('input[data-cart-status]'),
        cart = e.target

    if (cart.hasAttribute('checked'))
    {
      cart.removeAttribute('checked');
      let tbody = document.getElementById(`tbody-${cart.dataset.cart}`);
      tbody.setAttribute('data-cart-status', 'inactive');

      if (cartStatusSet.length ==  2)
      {
        cartStatusSet.forEach((cartStatus, i) =>
        {
          if (cartStatus != cart)
          {
            cartStatus.checked = true;

            let tbody = document.getElementById(`tbody-${cartStatus.dataset.cart}`);
            tbody.setAttribute('data-cart-status', 'active');

            cartStatus.setAttribute('checked','');
          }
        });
      }
    }
    else if( cart.hasAttribute('checked') == false)
    {
      cart.setAttribute('checked','');
      let tbody = document.getElementById(`tbody-${cart.dataset.cart}`);
      tbody.setAttribute('data-cart-status', 'active');

      if (cartStatusSet.length ==  2)
      {
        cartStatusSet.forEach((cartStatus, i) =>
        {
          if (cartStatus != cart)
          {
            cartStatus.checked = false;

            let tbody = document.getElementById(`tbody-${cartStatus.dataset.cart}`);
            tbody.setAttribute('data-cart-status', 'inactive');

            if (cartStatus.hasAttribute('checked'))
              cartStatus.removeAttribute('checked');
          }
        });
      }
    }

    deactivateCart();
  }
});
