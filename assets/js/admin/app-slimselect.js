// assets/js/admin/app-slimselect.js
import SlimSelect from 'slim-select';
import {addGrid} from './product/add-grid.js';
import applyIcon from './product/brand-icon.js';
import {removeGrid} from './product/remove-grid.js';
import {excludeColor} from './product/exclude-color.js';
import {resetOtherColors} from './product/reset-other-colors.js';
import { isEmpty as empty }  from '@zerodep/is-empty';
import {applyColor, applyColorLabel, applyExcludeColor} from './product/apply-color.js';

/* Activate SlimSelect for various pages */
// Admin Product New Page
// if (document.querySelector('form[name="product_form"]') !== null)
// {
//     let selectSet = document.querySelector('form[name="product_form"]').querySelectorAll('select');
//
//     selectSet.forEach((select, i) =>
//     {
//       let label = '', deselect = true, applyPatch = false, exclude = false,
//           search = true, slim = true, otherColor = false, addSquare = false, removeSquare = false;
//
//       if (select.dataset.hasOwnProperty('label'))
//         label = select.dataset.label;
//
//       if (select.dataset.hasOwnProperty('applyColor'))
//         applyPatch = true;
//
//       if (select.dataset.hasOwnProperty('addGrid'))
//         addSquare = true;
//
//       if (select.dataset.hasOwnProperty('removeGrid'))
//         removeSquare = true;
//
//       if (select.dataset.hasOwnProperty('excludeColor'))
//         exclude = true;
//
//       if (select.dataset.hasOwnProperty('deselect'))
//         if (select.dataset.deselect == 'false')
//         deselect = false;
//
//       if (select.dataset.hasOwnProperty('search'))
//         if (select.dataset.search == 'false')
//         search = false;
//
//       if (select.dataset.hasOwnProperty('slim'))
//         if (select.dataset.slim == 'false')
//           slim = false;
//
//       if (select.dataset.hasOwnProperty('otherColorSelect'))
//         otherColor = true;
//
//       if (slim)
//       {
//         new SlimSelect(
//         {
//           select: `#${select.id}`,
//           settings: {
//             placeholderText: label,
//             allowDeselect: deselect,
//             showSearch: search,
//           },
//           events: {
//             beforeChange: (option, oldOption) =>
//             {
//               if (applyPatch)
//                 applyColor(option, select.id);
//
//               if (select.id == 'product_form_brand')
//                 applyIcon(option);
//
//               if (removeSquare)
//                 if(!empty(oldOption[0].value))
//                   removeGrid(oldOption[0].value, select.id);
//
//               return true;
//             },
//             afterChange: (option) =>
//             {
//               if (exclude)
//                 excludeColor(select);
//
//               if (applyPatch)
//                 applyColorLabel(option, select.id);
//
//               if (document.getElementById(select.id).dataset.exclude == '')
//                 applyExcludeColor(select);
//
//               let qty = document.getElementById(select.dataset.input);
//               if (addSquare)
//                 if (!empty(option))
//                   addGrid(qty);
//
//               if (otherColor)
//                 resetOtherColors(select.dataset.otherColors);
//
//               return true;
//
//               // let similarColors = document.getElementById('product_form_similarProduct_colors'),
//               // colorSelectSet = pc.querySelectorAll('select[data-color-select=""]'),
//               // similarFabrics = document.getElementById('product_form_similarProduct_fabrics'),
//               // fabricSelectSet = pc.querySelectorAll('select[data-fabrics-select=""]'),
//               // similarTextures = document.getElementById('product_form_similarProduct_textures'),
//               // textureSelectSet = pc.querySelectorAll('select[data-textures-select=""]');
//               //
//               // /* Reset similar select */
//               // // similarColors.selectedIndex = -1;
//               // // similarFabrics.selectedIndex = -1;
//               // // similarTextures.selectedIndex = -1;
//               //
//               // if (document.getElementById(select.id).dataset.colorSelect == 'true')
//               //   replicateMultipleSelection(colorSelectSet, similarColors);
//               //
//               // if (document.getElementById(select.id).dataset.fabricsSelect == 'true')
//               //   replicateMultipleSelection(fabricSelectSet, similarFabrics);
//               //
//               // if (document.getElementById(select.id).dataset.texturesSelect == 'true')
//               //   replicateMultipleSelection(textureSelectSet, similarTextures);
//             }
//           }
//         });
//       }
//     });
// }

if (document.querySelector('[slim-select]') !== null)
{
    let selectTags = document.querySelectorAll('[slim-select]');

    selectTags.forEach((select) =>
    {
      let slim = new SlimSelect({
        select: '#' + select.id,
        settings: {
          showSearch: select.dataset.showSearch === 'true',
          searchText: select.dataset.searchMsg,
          searchPlaceholder: 'search...',
          placeholderText: select.dataset.placeholder,
          searchHighlight: select.dataset.searchHighlight === 'true',
          allowDeselect: select.dataset.allowDeselect === 'true',
        }
      });

      if (empty(select.dataset.selected))
        slim.setSelected('');

    });
}
