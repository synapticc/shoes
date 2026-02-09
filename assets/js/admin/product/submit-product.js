// assets/js/admin/product/submit-product.js



if (document.querySelector('form[name="product_form"]') !== null)
{

  let message = document.getElementById('duplicate-colors-msg'),
      productForm = document.querySelector('form[name="product_form"]');

  /* On form submit, check if there is duplicate selected options  */
  productForm.addEventListener('submit', e => {

      let pc = document.getElementById('product_form_productColor'),
        pcSet = pc.querySelectorAll('[pc-field=""]'),
        selectedValuesArray = [];

      // Retrieve selected options from the respective <select> tag
      // and store them in an array to look for duplicate combined values.

      pcSet.forEach((pc, pcCount) =>
      {
          let selectSet = pc.querySelectorAll('select'),
              selectedOptionsArray = [], selectedValues,
              color1 = selectSet[0],color2 = selectSet[1],color3 = selectSet[2],
              fabrics = selectSet[3], textures = selectSet[4];

          if (color1.selectedOptions[0].value != '' &&
              color2.selectedOptions[0].value != '' &&
              color3.selectedOptions[0].value != '')
          {
              selectedValues = color1.selectedOptions[0].value + '-'
                             + color2.selectedOptions[0].value + '-'
                             + color3.selectedOptions[0].value;

              let selectedFabricsArray = Array.from(fabrics.selectedOptions)
              .map(option => option.value),
              selectedFabrics = selectedFabricsArray.join("_");

              selectedValues = `${selectedValues}_${selectedFabrics}`;

          }
          else if (
              color1.selectedOptions[0].value != '' &&
              color2.selectedOptions[0].value != '' &&
              color3.selectedOptions[0].value == '')
          {
              selectedValues = color1.selectedOptions[0].value + '-'
                             + color2.selectedOptions[0].value;

              let selectedFabricsArray = Array.from(fabrics.selectedOptions)
              .map(option => option.value),
              selectedFabrics = selectedFabricsArray.join("_");

              selectedValues = `${selectedValues}_${selectedFabrics}`;

          }
          else if (
              color1.selectedOptions[0].value != '' &&
              color2.selectedOptions[0].value == '' &&
              color3.selectedOptions[0].value == '')
          {
              selectedValues = color1.selectedOptions[0].value;

              if (selectSet[3].selectedOptions.length > 0)
              {
                let selectedFabricsArray = Array.from(fabrics.selectedOptions)
                .map(option => option.value),
                selectedFabrics = selectedFabricsArray.join("_");
                selectedValues = `${selectedValues}_${selectedFabrics}`;
              }
          }
          selectedValuesArray.push(selectedValues);
      });

      // Check for unique values
      const uniqueSelectedValuesArray = Array.from(new Set(selectedValuesArray));

      if(selectedValuesArray.length === uniqueSelectedValuesArray.length)
      {
        if (!message.classList.contains('hide'))
          message.classList.add('hide');
      }
      else
      {
        if (message.classList.contains('hide'))
          message.classList.remove('hide');

        e.preventDefault();
      }
  });
}
