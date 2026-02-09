// assets/js/admin/product/similar.js

import empty from 'is-blank';

if (document.getElementById('product_form_similarProduct') !== null)
{
   let similarForm =  document.getElementById('product_form_similarProduct'),
       similarBrand = similarForm.querySelector('[data-label="brands"]'),
       brand =  document.getElementById('product_form_brand'),
       similarType = similarForm.querySelector('[data-label="types"]'),
       type =  document.getElementById('product_form_type'),
       similarOccasion = similarForm.querySelector('[data-label="occasions"]'),
       occasion =  document.getElementById('product_form_occasion')
      ;

  var replicateSelection = (select, similarSelect) =>
  {
    let selected = select.options[select.selectedIndex].value;

    similarSelect.options.forEach((option, i) =>
    {
      if (option.value === selected)
      {
        option.setAttribute('selected', 'selected');
        option.selected = true;
      }
    });
  };


  /* Once colors, fabrics or textures have been  selected, replicate
     them in the similar colors, fabrics or textures section.
  */
  var replicateMultipleSelection = (select, similarSelect) =>
  {
    select.selectedOptions.forEach((selected, i) =>
    {
      similarSelect.options.forEach((option, i) =>
      {
        if (selected.value === option.value)
        {
          option.setAttribute('selected', 'selected');
          option.selected = true;
        }
      });
    });
  };

  var applyIcon =  (option) =>
  {
    let icon = document.getElementById('brand-icon');

    if (!empty(option))
    {
      let brand = option.value,
          fullBrand = option.innerHTML;

      if (!empty(brand))
      {
        icon.setAttribute('title', fullBrand);
        icon.style.backgroundImage = "url(" + document.location.origin + "/build/images/" + brand + ".webp)";
      }
      else
      {
        icon.style.backgroundImage = "url(" + document.location.origin + "/build/images/empty-image.svg";

        icon.setAttribute('title', '');
      }
    }
  }


  brand.addEventListener('change', () => {
    let selected = brand.options[brand.selectedIndex];
    applyIcon(selected);
    replicateSelection(brand, similarBrand);
  });
  type.addEventListener('change', () => replicateSelection(type, similarType));
  occasion.addEventListener('change', () => replicateMultipleSelection(occasion, similarOccasion));
}

export default replicateSelection;
