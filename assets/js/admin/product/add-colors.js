// assets/js/admin/product/add-colors.js

import SlimSelect from 'slim-select';
import htmx from "htmx.org";
import {applyColor, applyColorLabel, applyExcludeColor} from './apply-color.js';
import replicateSelection from './similar.js';

/*
  Page:  Admin Product New Page
  Route: admin_product_new

  NOTE:
  <SELECT> RENDER ALTERNATIVES
    1) SlimSelect
      - Most complete in features.
      - Event (change) is fired only through its own method (beforeChange, applyExcludeColor).
    2) NiceSelect
      - Lacks the deselect feature
      - Once an option is selected, the font of the previous option remains bold even if another option is selected.
    3) LC-Select
      - Renders both text and image together in the dropdown-list.

  - SlimSelect has been used everywhere to render the <select> options
  Below, nice-select has been added as an alternative because with SlimSelect
  alone, the change in color <select> was not being detected in addEventListener.
  - nice-select lacks a 'deselect' feature, which has been added separately.
  - In this layout, I opted to place the color patch externally on the right.
*/
if (document.getElementById('product_form_productColor') !== null)
{
    var productForm = document.querySelector('form[name="product_form"]'),
        colorSelect = document.getElementById('product_data_form_color'),
        pc = document.getElementById('product_form_productColor'),
        colorDeleteSet;

    let addColorsBtn = document.getElementById('add-colors'),
        imageSet = document.querySelectorAll('#product-image-set'),
        searchResultDiv = document.getElementById('search-results');

    /* Once colors, fabrics or textures have been  selected, replicate
       them in the similar colors, fabrics or textures section.
    */
    var replicateMultipleSelection = (selectSet, similarSelect) =>
    {
      selectSet.forEach((select, i) =>
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
      });
    };

    /* Add new select and upload image option on clicking 'square-plus' button */
    addColorsBtn.addEventListener('click', (e) =>
    {
      /* Retrieve prototype for new productColor from HTML
         (which has been partly derived from Symfony own prototype
         generator).
      */
      // let newColor = pc.dataset.prototype;
      let newColor = document.getElementById('pc-prototype').innerHTML;

      /* Calibrate the index by replacing "__name__label__ __name__"
         by the loop's own index */
      if (pc.childElementCount == 0)
      { newColor = newColor.replace(/__name__label__/gi, 'Color 1');
        newColor = newColor.replace(/__name__/gi, 0);}
      else
      { newColor = newColor.replace(/__name__label__/gi, 'Color ' +  (pc.childElementCount + 1));
        newColor = newColor.replace(/__name__/gi, pc.childElementCount);}

      pc.insertAdjacentHTML('beforeend',newColor);
      colorDeleteSet =  document.querySelectorAll('#remove-color-btn');

      let newColorDiv = pc.lastElementChild,
          newColorSelectSet = newColorDiv.querySelectorAll('select');

      /* Activate SlimSelect for all new <select>*/
      newColorSelectSet.forEach((select, i) =>
      {
        new SlimSelect(
        {
          select: `#${select.id}`,
          settings:
          {
            allowDeselect: (i == 0) ? false : true,
            placeholderText: select.dataset.label
          },
          events:
          {
            // beforeChange: (option, oldOption) =>
            // {
              // if (document.getElementById(select.id).dataset.colorSelect == '')
              //   applyColor(option, select.id);
              //
              // return true;
            // },
            // afterChange: (option) =>
            // {
              // if (document.getElementById(select.id).dataset.colorSelect == '')
              //   applyColorLabel(option, select.id);
              //
              // let similarColors = document.getElementById('product_form_similarProduct_colors'),
              // colorSelectSet = pc.querySelectorAll('select[data-color-select=""]'),
              // similarFabrics = document.getElementById('product_form_similarProduct_fabrics'),
              // fabricSelectSet = pc.querySelectorAll('select[data-fabrics-select=""]'),
              // similarTextures = document.getElementById('product_form_similarProduct_textures'),
              // textureSelectSet = pc.querySelectorAll('select[data-textures-select=""]'),
              // similarTags = document.getElementById('product_form_similarProduct_tags'),
              // tagsSelectSet = pc.querySelectorAll('select[data-tags-select=""]')
              // ;
              //
              // if (document.getElementById(select.id).dataset.colorSelect == '')
              //   replicateSelection(select,similarColors);
              //
              // if (document.getElementById(select.id).dataset.fabricsSelect == '')
              //   replicateMultipleSelection(fabricSelectSet, similarFabrics);
              //
              // if (document.getElementById(select.id).dataset.texturesSelect == '')
              //   replicateMultipleSelection(textureSelectSet, similarTextures);
              //
              // if (document.getElementById(select.id).dataset.tagsSelect == '')
              //   replicateMultipleSelection(tagsSelectSet, similarTags);
              //
              // if (document.getElementById(select.id).dataset.excludeColorSelect == '')
              //   applyExcludeColor(select);
              //
              // return true;
            // }
          }
        });

        /*  Alternate option for SlimSelect */
        // NiceSelect.bind(
        //   newColorSelect,
        //   {  searchable: true, placeholder: 'Choose',
        //       searchtext: '', selectedtext: 'geselecteerd'});
      });

      let videoParent = newColorDiv.querySelector('[video-url=""]'),
          url = videoParent.querySelector('input'),
          embed = videoParent.querySelector('[embed-video=""]');

      /* htmx.process is used to enable HTMX on newly added elements.
         Otherwise htmx and hence AJAX won't work on new elements */
      htmx.process(newColorDiv);

      /*
        Add
          - image tag,
          - comment line
          - span tag (for label),
          - anchor tag for image,
          - cancel button & delete button
          - hidden input tag to delete image
      */
      let pcDivSet = pc.querySelectorAll('[pc-field]');

      pcDivSet.forEach((pcDiv, pcDivCount) =>
      {
        let imageInputSet = pcDiv.querySelectorAll('input[type="file"]');

        imageInputSet.forEach((imageInput,imageCount) =>
        {
          let imageLabel = imageInput.closest('div').querySelector('label'),
          countInput = imageInput.id.match(/\d+/g), existingName;

          if (!imageInput.closest('div').querySelector('span[id="upload-dialog"]'))
          {
            if (imageLabel.hasAttribute('for') == true)
            {
              /*  Add the comment at beginning of the parent div.
                  Ex. <!--Image 3 of set 2-->
              */
              let comment = document.createComment(`Image ${imageCount +1 } of set ${pcDivCount+1}`);
                newLabelSpan.closest('div').appendChild(comment);
                newLabelSpan.parentNode.insertBefore(comment, newLabelSpan);
            }
          }

          let productImage = imageInput.closest('div'),
              previewImage = productImage.querySelector('img'),
              cancelImage = productImage.querySelector('#cancel-image'),
              deleteImage = productImage.querySelector('#delete-image'),
              existingPath = previewImage.attributes.src.value,
              fileChosen = productImage.querySelectorAll('span')[1],
              imagePath = productImage.querySelector('a');


          cancelImage.addEventListener('click', (e) =>
          {
            e.preventDefault();
            /* Destroy previous local url */
            // URL.revokeObjectURL(_PREVIEW_URL);

            /* Show upload dialog button */
            // uploadDialog.style.display = 'inline-block';

            /* Reset to no selection */
            imageInput.value = '';

            if (existingName)
            {
              /* Set source of image and show */
              previewImage.setAttribute('src', existingPath);
              previewImage.setAttribute('data-src', existingPath);
              imagePath.setAttribute('href', existingPath);

              previewImage.attributes.src.value = existingPath;
              fileChosen.textContent = existingName;
              deleteImage.style.display = 'inline-block';
              deleteImage.querySelector('input').value = '';
            }

            if (existingPath == '')
            {
              deleteImage.style.display = 'none';
              previewImage.style.display = 'none';
              fileChosen.innerHTML = '<span style="font-size:16px;padding-left:20px;">No file chosen</span>';
            }

            /* Hide elements that are not required */
            // document.querySelector("#image-name").style.display = 'none';
            cancelImage.style.display = 'none';
            // document.querySelector("#upload-button").style.display = 'none';


            });

          deleteImage.addEventListener('click', (e) =>
          {
            e.preventDefault();
            deleteImage.style.display = 'none';
            previewImage.style.display = 'none';
            previewImage.attributes.src.value = '';
            fileChosen.innerHTML = '<span style="font-size:16px;padding-left:20px;">No file chosen</span>';

            /* Set hidden delete-image token */
            deleteImage.querySelector('input').value = true;

            /* Reset image input */
            imageInput.value = '';
          });
        });
      });
    });
}
