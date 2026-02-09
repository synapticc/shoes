// assets/js/store/nou-slider.js

import * as noUiSlider from 'nouislider';

/*
  Page:  Profile new review, Profile edit review
  Route: review_new || review_edit

  noUiSlider is a lightweight range slider.

  > When the slider value is changed, replicate the new  value
    to its corresponding input.

  https://github.com/leongersen/noUiSlider
*/

if (document.getElementById('review_form_fit'))
{
  const sliders = {
      fit: { input: 'review_form_fit', slider: 'review_form_slider_fit'},
      width: { input: 'review_form_width', slider: 'review_form_slider_width'},
      comfort: { input: 'review_form_comfort', slider: 'review_form_slider_comfort'},
    };

    for (let set in sliders)
    {
      let input = document.getElementById(sliders[set].input),
          slider = document.getElementById(sliders[set].slider),
          values='';

      if (slider.dataset.hasOwnProperty(set))
        values = Object.values(JSON.parse(slider.dataset[set]));

      let format =
        { to: (value) => values[Math.round(value)],
          from: (value) => values.indexOf(value)};

      noUiSlider.create(slider, {
          // start values are parsed by 'format'
          start: [values[input.value - 1]],
          range: { min: 0, max: values.length - 1 },
          behaviour: 'drag',
          step: 1,
          tooltips: false,
          format: format,
          pips: { mode: 'steps', format: format, density: 50 },
      })
      slider.noUiSlider.on("change",
            (a, b) => input.setAttribute('value', values.indexOf(a[0]) + 1));

      let sliderBtns = slider.querySelectorAll('.noUi-touch-area');

      sliderBtns.forEach((sliderBtn, i) =>
      {
        sliderBtn.setAttribute('title', 'Click & Drag');
      });

    }
}
