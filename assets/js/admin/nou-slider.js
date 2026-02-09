// assets/js/admin/nou-slider.js

import * as noUiSlider from 'nouislider';
/* noUiSlider is a lightweight range slider.

  https://github.com/leongersen/noUiSlider
*/
if (document.getElementById('input-slider-range'))
{

    let slider = document.getElementById("input-slider-range"),
        low = document.getElementById("input-slider-range-value-low"),
        e = document.getElementById("input-slider-range-value-high"),
        f = [document, e];

    let minPrice = document.querySelector('input[data-min-price=""]'),
        maxPrice = document.querySelector('input[data-max-price=""]');

    noUiSlider.create(slider,
      { start: [2500, 6000],
        pips:
          { mode: 'range',
            density: 0.3,
            format: wNumb({
                decimals: 2,
                prefix: 'Rs '}),
            mode: 'positions',
            values: [0, 20, 50, 70, 100],
        },
        behaviour: 'drag-smooth-steps-tap',
        connect: !0,
        step: 2,
        tooltips: true,
        range:
        { min: 500,
          max: 25000}
      }),
      slider.noUiSlider.on("update", (a, b) => f[b].textContent = a[b]),

      // Update min and max input
      slider.noUiSlider.on("change", (a, b) =>
      {
        minPrice.value = a[0];
        maxPrice.value = a[1];
      });
}
