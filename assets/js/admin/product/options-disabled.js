// assets/js/admin/product/options-disabled.js
/*
  Page:  Admin Product New Page
  Route: 1) admin_product_new
         2) admin_product_edit

  Disable female category options when type options 'Men' is selected.
 */
  if (document.querySelector('#product_form_category') !== null)
  {
      let category = document.querySelector('#product_form_category'),
      occasion = document.querySelector('#product_form_occasion'),
      type = document.querySelector('#product_form_type'),
      womenOptions =
        ['pumps', 'peep_toes', 'heels','wedge_sandals', 'platform_sandals',
         'wedge_pumps'],
      nonMenOptions = ['women', 'kids', 'adults', ''];

      category.addEventListener('change', () =>
      {
          if (category.options[category.selectedIndex])
          {
            if (category.options[category.selectedIndex].value === 'men')
            {
                type.options.forEach((optionType, i) =>
                {
                  // Disable women options.
                  if (womenOptions.includes(optionType.value))
                    optionType.setAttribute('disabled','');
                });
            }
            else if (nonMenOptions.includes(category.options[category.selectedIndex].value))
            {
                type.options.forEach((optionType, i) =>
                {
                  // Enable back disabled options.
                  if (womenOptions.includes(optionType.value))
                    if (optionType.hasAttribute('disabled'))
                      optionType.removeAttribute('disabled');
                });
            }
          }
        });
  }
