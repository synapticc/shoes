// assets/js/admin/product/add-features.js

import {removeSqrInvBtn} from '../../module';

/* Add new input fields for features */
if (document.querySelector('#add-features') !== null)
{
    let addFeaturesBtn = document.querySelector('#add-features'),
        features = document.querySelector('#product_form_features'),
        counter = 1,
        existingCounter = [];

    addFeaturesBtn.addEventListener('click', () =>
    {
      let newFeature = features.dataset.prototype;
      /* newFeature :
            <div>
              <label for="product_form_features_1" class="required">Feature 1</label>
              <input type="text" id="product_form_features_1" name="product_form[features][1]" required="required" class="p-2 form-control">
            </div>
      */
      if (features.childElementCount > 0)
      {
        features.children.forEach((feature, i) =>
        {
          existingCounter = existingCounter.concat(feature.querySelector('input').name.match(/\d/g)[0]);
        });
      }

      if (existingCounter.length > 0)
        counter =   Math.max(...existingCounter) + 1;

      newFeature = newFeature.replace(/__name__label__/gi, 'Feature ' + counter);
      newFeature = newFeature.replace(/__name__/gi, counter);

      let newDiv = document.createElement('div');
      newDiv.setAttribute('class','row align-items-center p-1 mb-1 feature-list');
      newDiv.insertAdjacentHTML('beforeend',newFeature);
      newDiv.firstElementChild.setAttribute('class','col-10');

      newDiv.insertAdjacentHTML('afterbegin', '<span class="col-1 text-center" title="Move up or down."><i class="fas fa-arrows-alt handle"></i></span>');
      newDiv.insertAdjacentHTML('beforeend', '<span title="Remove feature." class="col-1 remove-feature-btn" data-remove-feature>' + removeSqrInvBtn + '</span>');

      // Add the attribute data-remove-feature to <svg> and <path>
      newDiv.querySelector('svg').setAttribute('data-remove-feature','');
      newDiv.querySelector('path').setAttribute('data-remove-feature','');

      features.appendChild(newDiv);
      counter++;

      let featureInputAll = features.querySelectorAll('input');
      featureInputAll.forEach((featureInput, i) =>
      {
        featureInput.setAttribute('class','form-control');
      });


      if (features.childElementCount > 0)
      {
        features.children.forEach((feature, i) =>
        {
          let label = feature.querySelector('label'),
              input = feature.querySelector('input');


          label.attributes.for.value = label.attributes.for.value.replace(/\d/gi, i+1);
          label.innerText = 'Feature ' + (i+1);

          input.attributes.id.value = input.attributes.id.value.replace(/\d/gi, i+1);
          input.attributes.name.value = input.attributes.name.value.replace(/\d/gi, i+1);

          if (input.attributes.value)
            input.attributes.value.value = input.attributes.value.value.replace(/\d/gi, i+1);
        });
      }
    });

    /* Displace last selected option to the last position of respective <optgroup> */
    document.addEventListener('click', e => {

        if(e.target.dataset.removeFeature == '')
        {
            let featureRemoveBtn = e.target;
            let featureList =  featureRemoveBtn.closest('div.feature-list');

            featureList.remove();

            if (features.childElementCount > 0)
            {
                features.children.forEach((feature, i) =>
                {
                    let label = feature.querySelector('label');
                    let input = feature.querySelector('input');

                    label.attributes.for.value = label.attributes.for.value.replace(/\d/gi, i+1);
                    label.innerText = 'Feature ' + (i+1);

                    input.attributes.id.value = input.attributes.id.value.replace(/\d/gi, i+1);
                    input.attributes.name.value = input.attributes.name.value.replace(/\d/gi, i+1);

                    if (input.attributes.value)
                    {
                        input.attributes.value.value = input.attributes.value.value.replace(/\d/gi, i+1);
                    }

                });
            }

         }
    });

    if (features.childElementCount >= 1)
    {
        features.children.forEach((feature, i) =>
        {
            feature.setAttribute('class','row align-items-center p-1 mb-1 feature-list');
            let label = feature.querySelector('label');
            label.innerText = 'Feature ' + label.innerText;
            /*
              "<label for=\"product_form_features_1\">Feature 1</label><input type=\"text\" id=\"product_form_features_1\" name=\"product_form[features][1]\" class=\"form-control\" value=\"Simple slip-on design\">"
            */
            let newDiv = document.createElement('div');
            newDiv.insertAdjacentHTML('beforeend', feature.innerHTML);
            newDiv.setAttribute('class','col-10');

            feature.innerHTML = newDiv.outerHTML;

            feature.insertAdjacentHTML('afterbegin', '<span class="col-1 text-center" title="Move up or down"><i class="fas fa-arrows-alt handle"></i></span>');
            feature.insertAdjacentHTML('beforeend', '<span title="Remove feature." class="col-1 remove-feature-btn" data-remove-feature>' + removeSqrInvBtn + '</span>');

            // Add the attribute data-remove-feature to <svg> and <path>
            feature.querySelector('svg').setAttribute('data-remove-feature','');
            feature.querySelector('path').setAttribute('data-remove-feature','');
        });
    }
}
