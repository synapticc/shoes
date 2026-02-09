// assets/js/admin/product/brand-icon.js

import {empty} from '../../module';

/* Display the brand icon selected on its right side.
Argument:
  option: The option selected.
  id: The id of the <select> being fired.
   */
if (document.getElementById('product_form_brand') !== null)
{
  var applyIcon =  (option, id) =>
  {
    let icon = document.getElementById('brand-icon');

    if (!empty(option))
    {
      let brand = option[0].value,
          fullBrand = option[0].text;

      icon.setAttribute('title', fullBrand);
      icon.style.backgroundImage = "url(" + document.location.origin + "/build/images/" + brand + ".webp)";
    }

    if (empty(option))
    {
      icon.style.backgroundImage  = '';
      icon.setAttribute('title', '');
    }
  }
}

export default applyIcon;
