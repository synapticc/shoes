// assets/js/admin/product_data/product-detail.js

import SlimSelect from 'slim-select';
import { isEmpty as empty }  from '@zerodep/is-empty';

if (document.getElementById('admin_product_data_new') !== null ||
    document.getElementById('admin_product_data_edit') !== null)
{
    let supplierSelect = document.getElementById('product_data_form_supplier_supplier'),
      colorSelect = document.getElementById('product_data_form_color'),
      sizeSelect = document.getElementById('product_data_form_size'),
      sellingPrice = document.getElementById('product_data_form_sellingPrice'),
      selectThumbnail = document.querySelectorAll('#select-thumbnail'),
      productDataForm = document.querySelector('form[name="product_data_form"]'),
      supplierObj = null, supplierQtyObj,
      supplierSelected,selectedSupplierSet,
      originalSize, originalColor, originalPrice,
      selectedSizesSet, sizesFullArray, sizesObj,
      allSizesObj, allColorsObj,
      selectSizeArray = [],fullSizesArray = []
    ;

    let
      colorSlimSelect =
        new SlimSelect({
          select: '#product_data_form_color'
        }),
      sizeSlimSelect =
        new SlimSelect({
          select: '#product_data_form_size'
        }),
      supplierSlimSelect =
        new SlimSelect({
          select: '#product_data_form_supplier_supplier',
          placeholder: 'Choose supplier',
          showSearch: true,
          searchText: 'Sorry, couldn\'t find anything',
          settings: {
            allowDeselect: true
          }
        })
    ;


    /* Disable 1) size <select>
               2) color <select> on loading itself. */
    if (document.getElementById('admin_product_data_new') !== null)
    {
      colorSlimSelect.disable(); sizeSlimSelect.disable();
    }

    if (supplierSelect.dataset.hasOwnProperty('suppliers'))
      if (supplierSelect.dataset.suppliers != '')
        supplierObj = JSON.parse(supplierSelect.dataset.suppliers);

    if (supplierSelect.dataset.hasOwnProperty('sizes'))
    {
      if (supplierSelect.dataset.sizes != '')
      {
        sizesObj = JSON.parse(supplierSelect.dataset.sizes);
        sizesFullArray = Object.values(sizesObj);
        // sizesFullArray = Object.values(sizesObj).map(Number);
        // since the element in the above array are in string
        // format, map(Number) is used to convert each of them into integer
        // for easy comparison below.
      }
    }

    if (supplierSelect.dataset.hasOwnProperty('allSizes'))
      if (supplierSelect.dataset.allSizes != '')
        allSizesObj = JSON.parse(supplierSelect.dataset.allSizes);

    if (supplierSelect.dataset.hasOwnProperty('allColors'))
      if (supplierSelect.dataset.allColors != '')
        allColorsObj = JSON.parse(supplierSelect.dataset.allColors);

    if (supplierSelect.dataset.hasOwnProperty('qtyBySupplier'))
      if (supplierSelect.dataset.qtyBySupplier != '')
        supplierQtyObj = JSON.parse(supplierSelect.dataset.qtyBySupplier);

    if (sizeSelect.dataset.hasOwnProperty('originalSize'))
        originalSize = sizeSelect.dataset.originalSize;

    if (sizeSelect.dataset.hasOwnProperty('originalColor'))
        originalColor = sizeSelect.dataset.originalColor

    if (sellingPrice.dataset.hasOwnProperty('originalPrice'))
        originalPrice = sellingPrice.dataset.originalPrice

    supplierSelect.options.forEach((supplierOption, i) =>
    {
      if (!empty(supplierQtyObj))
      {
        if (supplierObj.hasOwnProperty(supplierOption.value))
        {
          if (supplierQtyObj.hasOwnProperty(Number(supplierOption.value)))
            supplierOption.innerHTML += `  (${supplierQtyObj[Number(supplierOption.value)]} pcs)`;

          let colorSet = supplierObj[supplierOption.value],
              colors = Object.keys(colorSet),
              allColors = Object.keys(allColorsObj);

          let  difference = allColors.filter(x => !colors.includes(x));

          if (empty(difference))
          {
            let differenceArray = [];
            for (var color of Object.keys(colorSet))
            {
              let sizes = colorSet[color],
                  allSizes = Object.values(allSizesObj).map(String),
                  difference = allSizes.filter(x => !sizes.includes(x));
              differenceArray.push(difference);
            }
            if (empty(differenceArray))
              if (!supplierOption.hasAttribute('disabled'))
                supplierOption.setAttribute('disabled','');
          }
        }
      }
    });


    // On loading, Hide/reveal corresponding images set
    selectThumbnail.forEach((thumbnail, i) =>
    {
      let thumbnailColor = thumbnail.dataset.color,
        // select = document.getElementById('product_data_form_color'),
        // selectedColor = select.options[select.selectedIndex].value;
         selectedColor = colorSlimSelect.getSelected()[0];

      if (thumbnailColor == selectedColor)
      {
        if (thumbnail.classList.contains('hide'))
          thumbnail.classList.remove('hide');
      }
      else if (thumbnailColor != selectedColor)
      {
        if (!thumbnail.classList.contains('hide'))
          thumbnail.classList.add('hide');
      }
    });


    // When Supplier <select> changes
    supplierSelect.addEventListener('change', e =>
    {
      let selectedColor;

      // If no supplier is selected
      if (supplierSlimSelect.getSelected()[0] == '')
      {
        // If all suppliers are unselected
        if (colorSlimSelect.getSelected()[0] != '')
        {
          // Unselect all colors options
          colorSelect.nextElementSibling.querySelector('.ss-deselect').click();

          colorSlimSelect.disable();

          if (sizeSelect.selectedOptions.length != 0 )
            sizeSelect.selectedIndex = 0;

          // Disable all size options
          sizeSelect.options.forEach((optionSize, i) =>
          {
            if (!optionSize.hasAttribute('disabled'))
              optionSize.setAttribute('disabled','');

            // Remove previous price if present
            if (optionSize.value != '')
              optionSize.innerHTML =  `${optionSize.dataset.label}`;
          });
        }
      }
      // If a supplier is selected
      else if (supplierSlimSelect.getSelected()[0] != '')
      {
        colorSlimSelect.enable();
        supplierSelected = supplierSlimSelect.getSelected()[0];

        if (colorSelect.selectedOptions.length != 0)
          selectedColor = colorSlimSelect.getSelected()[0];

        if (!empty(supplierObj))
        {
          colorSelect.options.forEach((optionColor, optionColorX) =>
          {
            if (supplierObj.hasOwnProperty(supplierSelected))
            {
              selectedSupplierSet = supplierObj[supplierSelected];
              for (var color in selectedSupplierSet)
              {
                if (selectedSupplierSet.hasOwnProperty(color))
                {
                  let difference = sizesFullArray.filter(x => !selectedSupplierSet[color].includes(x));

                  if (color == optionColor.value)
                  {
                    if (empty(difference))
                    {
                      // Disable the enabled options
                      if (!optionColor.hasAttribute('disabled'))
                        optionColor.setAttribute('disabled','');

                      // Unselect any color or size option if present
                      if (colorSlimSelect.getSelected()[0] == optionColor.value )
                        if (colorSelect.selectedOptions.length != 0 )
                          colorSelect.selectedIndex = 0;
                    }
                    else
                    {
                      if (optionColor.hasAttribute('disabled'))
                        optionColor.removeAttribute('disabled');
                    }
                  }
                }
                if (!selectedSupplierSet.hasOwnProperty(optionColor.value))
                {
                  if (optionColor.hasAttribute('disabled'))
                    optionColor.removeAttribute('disabled');
                }
              }
            }
            else if (!supplierObj.hasOwnProperty(supplierSelected))
            {
              if (supplierSlimSelect.getSelected()[0] != '')
                if (optionColor.hasAttribute('disabled'))
                  optionColor.removeAttribute('disabled');
            }

            sizeSelect.options.forEach((optionSize) =>
            {
              // If no color is selected
              if (colorSlimSelect.getSelected()[0] == '')
              {
                // Disable all size options
                if (!optionSize.hasAttribute('disabled'))
                  optionSize.setAttribute('disabled','');

                if (optionSize.value != '')
                  optionSize.innerHTML =  `${optionSize.dataset.label}`;
              }
                // If a color is selected
              else if(colorSlimSelect.getSelected()[0] != '')
              {
                // Enable all size options
                if (optionSize.hasAttribute('disabled'))
                  optionSize.removeAttribute('disabled');

                if (optionSize.value != '')
                  optionSize.innerHTML =  `${optionSize.dataset.label}`;
              }
            });
          });

          selectedSupplierSet = [];
          if (supplierObj.hasOwnProperty(supplierSelected))
          {
            selectedSupplierSet = supplierObj[supplierSelected];
            if (selectedSupplierSet.hasOwnProperty(selectedColor))
            {
              selectedSizesSet = selectedSupplierSet[selectedColor];
              sizeSelect.options.forEach((optionSize, i) =>
              {
                if (!selectedSizesSet.includes(optionSize.value))
                {
                  if (optionSize.hasAttribute('disabled'))
                    optionSize.removeAttribute('disabled');
                }
                else if (selectedSizesSet.includes(optionSize.value))
                {
                  if (document.getElementById('admin_product_data_new') !== null)
                    if (sizeSelect.selectedOptions.length != 0 )
                      if (sizeSelect.selectedOptions[0] == optionSize )
                        sizeSelect.selectedIndex = -1;

                  if (!optionSize.hasAttribute('selected'))
                    if (!optionSize.hasAttribute('disabled'))
                      optionSize.setAttribute('disabled','');

                  // Append price of counterpart productData
                  let set = sizesObj[selectedColor],
                      indexSize = Object.keys(set).map(String).indexOf(optionSize.value),
                      price = Number.parseFloat( Object.values(set)[indexSize]).toFixed(2);

                  optionSize.innerHTML =  `${optionSize.dataset.label} (Rs ${price})`;
                }
              });
            }
            else if (!selectedSupplierSet.hasOwnProperty(selectedColor))
            {
              // Enable back all the options
              sizeSelect.options.forEach((optionSize, i) =>
              {
                if (optionSize.hasAttribute('disabled'))
                  optionSize.removeAttribute('disabled');
              });
            }
          }
        }
        else if(empty(supplierObj))
        {
          // Enable all color options
          colorSelect.options.forEach((optionColor, optionColorCount) =>
          {
            if (optionColor.hasAttribute('disabled'))
              optionColor.removeAttribute('disabled');
          });

          // Enable all size options
          sizeSelect.options.forEach((optionSize) =>
          {
            if (optionSize.hasAttribute('disabled'))
              optionSize.removeAttribute('disabled');
          });
        }
      }
    });


    // When Color <select> changes
    colorSelect.addEventListener('change', e =>
    {
        let selectedColor, selectedSize;

        // If no color is selected
        if (colorSlimSelect.getSelected()[0] == '' )
        {
          sizeSelect.selectedIndex = 0;
          sizeSlimSelect.disable();
          sizeSelect.options.forEach((optionSize, i) =>
          {
            // Re-disable the enabled options
            if (!optionSize.hasAttribute('disabled'))
              optionSize.setAttribute('disabled','');

            if (optionSize.value != '')
              optionSize.innerHTML =  `${optionSize.dataset.label}`;

          });
        }
        // If a color is selected
        else if (colorSlimSelect.getSelected()[0] != '' )
        {
          sizeSlimSelect.enable();
          sizeSelect.options.forEach((optionSize, i) =>
          {
            // Re-enable the disbled size options
            if (optionSize.hasAttribute('disabled'))
              optionSize.removeAttribute('disabled');

            if (optionSize.value != '')
              optionSize.innerHTML =  `${optionSize.dataset.label}`;
          });
        }
        //
        supplierSelected = supplierSlimSelect.getSelected()[0];
        selectedColor = colorSlimSelect.getSelected()[0];
        // selectedColor = colorSelect.options[colorSelect.selectedIndex].dataset.color;
        selectedSize = sizeSlimSelect.getSelected()[0];


        if (!empty(supplierObj))
        {
          if (supplierObj.hasOwnProperty(supplierSelected))
          {
            selectedSupplierSet = supplierObj[supplierSelected];
            if (selectedSupplierSet.hasOwnProperty(selectedColor))
            {
              selectedSizesSet = selectedSupplierSet[selectedColor];
              sizeSelect.options.forEach((optionSize, i) =>
              {
                if (!selectedSizesSet.includes(optionSize.value))
                {
                  if (optionSize.hasAttribute('disabled'))
                    optionSize.removeAttribute('disabled');

                  if (optionSize.value != '')
                    optionSize.innerHTML =  `${optionSize.dataset.label}`;
                }
                else if (selectedSizesSet.includes(optionSize.value))
                {
                  if (sizeSelect.selectedOptions.length != 0 )
                    if (sizeSelect.selectedOptions[0] == optionSize )
                      sizeSelect.selectedIndex = -1;

                  // Re-disable the enabled options
                  if (!optionSize.hasAttribute('disabled'))
                    optionSize.setAttribute('disabled','');


                  // Append the stored price to the size displayed
                  let set = sizesObj[selectedColor],
                      // indexSize = Object.keys(set).map(Number).indexOf(Number(optionSize.value)),
                      indexSize = Object.keys(set).map(String).indexOf(optionSize.value),
                      price = Number.parseFloat( Object.values(set)[indexSize]).toFixed(2);

                  optionSize.innerHTML =  `${optionSize.dataset.label} (Rs ${price})`;
                }
              });
            }
            else if (!selectedSupplierSet.hasOwnProperty(selectedColor))
            {
            //   sizeSelect.options.forEach((optionSize, i) =>
            //   {
            //     // Enable back all the options
            //     if (optionSize.hasAttribute('disabled'))
            //       optionSize.removeAttribute('disabled');
            //
            //     if (optionSize.value != '')
            //       optionSize.innerHTML =  `${optionSize.dataset.label}`;
            //   });
            }
          }
          else if (!supplierObj.hasOwnProperty(supplierSelected))
          {
            sizeSelect.options.forEach((optionSize, i) =>
            {
              // Enable back all the options
              if (optionSize.hasAttribute('disabled'))
                optionSize.removeAttribute('disabled');

              if (optionSize.value != '')
                optionSize.innerHTML =  `${optionSize.dataset.label}`;
            });
          }
        }

        // Hide/reveal corresponding images set
        selectThumbnail.forEach((thumbnail, i) =>
        {

          let thumbnailColor = thumbnail.dataset.color;
          if (thumbnailColor == selectedColor)
          {
            if (thumbnail.classList.contains('hide'))
              thumbnail.classList.remove('hide');
          }
          else if (thumbnailColor != selectedColor)
          {
              if (!thumbnail.classList.contains('hide'))
                thumbnail.classList.add('hide');
          }
        });
    });

    /* When a size Color:is selected, check if a product, of the same
     * color and size and different supplier, has been registered.
     *
     * Replicate the same selling price.
     */
    sizeSelect.addEventListener('change', e =>
    {
      let selectedSupplier, selectedColor, selectedSize;

      if (!empty(sizesObj))
      {
        // selectedColor = colorSelect.options[colorSelect.selectedIndex].dataset.color;
        selectedColor = colorSlimSelect.getSelected()[0];
        selectedSize = sizeSlimSelect.getSelected()[0];

        if (sizesObj.hasOwnProperty(selectedColor))
        {
          let set = sizesObj[selectedColor],
              hasValue = Object.keys(set).map(String).includes(selectedSize);

          if (hasValue)
          {
             let sizesSet = Object.keys(set).map(String),
                 indexSize = sizesSet.indexOf(selectedSize),
                 price = Number.parseFloat( Object.values(set)[indexSize]).toFixed(2);

             sellingPrice.setAttribute('value',price);

             if (document.getElementById('admin_product_data_new') !== null)
              sellingPrice.setAttribute('disabled','');

          }
          else if (!hasValue)
          {
            if (originalPrice === null)
            {
              sellingPrice.value = '';
              if (sellingPrice.hasAttribute('disabled'))
                sellingPrice.removeAttribute('disabled');
            }
          }
        }
      }
    });

   let enablePrice = () =>
   {
      if (sellingPrice.hasAttribute('disabled'))
        sellingPrice.removeAttribute('disabled');
   }

   productDataForm.addEventListener('submit', enablePrice);
}
