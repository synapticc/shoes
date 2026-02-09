// assets/js/store/cart/update-top.js


export default function updateTopCart()
{
  let target = document.getElementById('cart-listing');
  // Update top cart
    let update = `/update-cart`;
    fetch(update).
    then(response => response.json()).
    then(data => target.outerHTML = data);
}
