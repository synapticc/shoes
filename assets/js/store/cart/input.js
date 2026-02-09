// assets/js/store/cart/input.js


document.addEventListener(
  'keydown',
  (e) =>
{
  if (e.target.dataset.cartQty == '')
    if (e.key === 'ArrowUp' || e.key === 'ArrowDown')
      e.preventDefault();

});


document.addEventListener(
  'wheel',
  (e) =>
{
  if (e.target.dataset.cartQty == '')
    e.preventDefault();
});
