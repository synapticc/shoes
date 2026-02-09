// assets/js/store/filter.js
/*
  Page:  Store Listing Main Page
*/
if (document.querySelector('#filter') !== null)
{
    /* Submit filter form on clicking checkbox  */
    let filter = document.getElementById('filter'),
        filterExclude = document.getElementById('filter_form_color_exclude'),
        filterInputAll =  document.querySelectorAll('input[filter]'),
        resetFilterAll = filter.querySelectorAll('#square-reset-filter');

    // Remove all query parameters
    if (document.getElementById('filter-reset-all') !== null)
    {
      let resetAllParams = document.getElementById('filter-reset-all');

      // Remove all query parameters
      resetAllParams.addEventListener('click', e => {
          window.document.location.href = window.document.location.pathname;
      });
    }

    const url = new URL(window.document.location);
    let searchParams = new URLSearchParams(url.search);

    resetFilterAll.forEach((resetFilter, i) =>
    {
      resetFilter.addEventListener('click', e => {
        let paramReset = resetFilter.dataset.filter;

        if (paramReset !== 'price')
        {
          if (paramReset == 'status')
          {
            if (searchParams.has('like[]') === true)
              searchParams.delete('like[]');

            if (searchParams.has('delivery[]') === true)
              searchParams.delete('delivery[]');

            if (searchParams.has('recommend[]') === true)
              searchParams.delete('recommend[]');
          }

          if (searchParams.has(`${paramReset}[]`)  === true)
            searchParams.delete(`${paramReset}[]`);

          if (searchParams.has(`${paramReset}`)  === true)
            searchParams.delete(`${paramReset}`);
        }
        else if (paramReset === 'price')
        {
          if (searchParams.has("price[min]"))
            searchParams.set('price[min]', 500);

          if (searchParams.has("price[min]"))
            searchParams.set('price[max]', 25000);

          if (searchParams.has("price_range[]"))
            searchParams.delete('price_range[]');
        }
        if (paramReset === 'color')
        {
          if (searchParams.has("color_exclude[]"))
            searchParams.delete('color_exclude[]');
        }


        if (url !== searchParams.toString() )
        {
          let newURL =   window.document.location.pathname
                           +
                          '?'
                           +
                          searchParams.toString();

          /* Redirect to new URL
          ex.
             /store
             ?
             price%5Border%5D=nameAsc%5Bmin%5D=500&price%5Bmax%5D=25000
          */
          window.document.location.href  = newURL;
        }
      });
    });

    /*
      By: cubertodesign
      https://codepen.io/kr4ik/pen/wLogWm
    */
    if (document.getElementById('min-slider') !== null &&
        document.getElementById('max-slider') !== null)
    {
      let minSlider = document.querySelector('#min-slider'),
          maxSlider = document.querySelector('#max-slider');

      function numberWithSpaces(number)
      {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
      }

      function updateDollars()
      {
        // let fromValue = (maxDollars - minDollars) * minSlider.value / 100 + minDollars;
        // let toValue = (maxDollars - minDollars) * maxSlider.value / 100 + minDollars;

        document.querySelector('#from').textContent = `Rs ${numberWithSpaces(Math.floor(minSlider.value))}`;
        document.querySelector('#to').textContent = `Rs ${numberWithSpaces(Math.floor(maxSlider.value))}`;
      }

      function cancelPriceRange()
      {
        let minValue = parseInt(minSlider.value),
            maxValue = parseInt(maxSlider.value),
            sliderStatus = document.getElementById('slider-status');

        if (searchParams.has('priceRange[]'))
            searchParams.delete('priceRange[]');

        // if (searchParams.has("price[min]")  === true)
        searchParams.set('price[min]', minValue);

        // if (searchParams.has("price[min]")  === true)
        searchParams.set('price[max]', maxValue);


        if (searchParams.size != 0 )
        {
          let newURL = `${window.document.location.pathname}?${searchParams.toString()}`;
          window.document.location.href  = newURL;
        }
        else { filter.submit();}

      }

      minSlider.addEventListener('input', e =>
      {
        let minValue = parseInt(minSlider.value),
            maxValue = parseInt(maxSlider.value);

        if (minValue > maxValue - 10)
        {
          maxSlider.value = minValue + 10;

          if (maxValue === parseInt(maxSlider.max)) {
            minSlider.value = parseInt(maxSlider.max) - 10;
          }
        }
        updateDollars();
      });

      maxSlider.addEventListener('input', e =>
      {
        let minValue = parseInt(minSlider.value),
            maxValue = parseInt(maxSlider.value);

        if (maxValue < minValue + 10) {
          minSlider.value = maxValue - 10;

          if (minValue === parseInt(minSlider.min)) {
            maxSlider.value = 10;
          }
        }
        updateDollars();
      });

      let sliderAll =  document.querySelectorAll('input[type="range"].slider-progress');

      sliderAll.forEach((slider, i) =>
      {
        slider.style.setProperty('--value', slider.value);
        slider.style.setProperty('--min', slider.min == '' ? '0' : slider.min);
        slider.style.setProperty('--max', slider.max == '' ? '100' : slider.max);
        slider.addEventListener('input', () => slider.style.setProperty('--value', slider.value));

        slider.addEventListener('input', e => {

            slider.style.setProperty('--value', slider.value);
            slider.style.setProperty('--value', slider.value);

            if (slider.id === 'max-slider')
            {
                maxSlider.title = `Max price: Rs ${slider.value}`;

                if (slider.value == minSlider.value)
                  minSlider.style.setProperty('--value', slider.value);
            }

            if (slider.id === 'min-slider')
            {
                minSlider.title = `Min price: Rs ${slider.value}`;

                if (slider.value == maxSlider.value)
                  maxSlider.style.setProperty('--value', slider.value);
            }
        });
      });

      minSlider.addEventListener('change', cancelPriceRange);
      maxSlider.addEventListener('change', cancelPriceRange);
    }

    filter.addEventListener('change', e =>
    {
      /* Submit for all changes except that of pricing.
      Pricing changes are handled in cancelPriceRange() above.
      */
      if (e.target.closest('#pricing-div') === null)
        filter.submit();

    });

    function disableColorList(options)
    {
      let exclude = options[options.length - 1].value;
          colorExlcude = document.getElementById(exclude),
          colorInput = colorExlcude.children[0];
          colorExlcudeHover = colorExlcude.nextElementSibling;

      colorExlcude.style.cursor = 'not-allowed';
      colorExlcudeHover.className = 'top-disabled';
      colorInput.setAttribute('disabled','');
    }

    filterInputAll.forEach((input, i) =>
    {
        input.addEventListener('click', () => {
          if (!input.hasAttribute('disabled')) filter.submit()});
    });

}
