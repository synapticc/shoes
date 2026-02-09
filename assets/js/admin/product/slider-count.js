// assets/js/admin/product/slider-count.js

function sliderCount()
{
  let count = document.getElementById('slider-count'),
  grid = document.getElementById('gridProduct');

  count.innerHTML = grid.children.length;
}

export {sliderCount};
