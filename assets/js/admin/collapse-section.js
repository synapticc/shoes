// assets/js/admin/collapse-section.js

import { isEmpty as empty }  from '@zerodep/is-empty';
/* When collapsing HTML section, change 'Arrow down icon' to 'Arrow up icon'.
   When expanding HTML section, change 'Arrow up icon' to 'Arrow down icon'.*/
document.addEventListener('click', e =>
{
    let arrow = e.target;

    if (arrow.id == 'arrow-button')
      replaceIcon(arrow);

    else if (!empty(arrow.closest("#arrow-button")))
      replaceIcon(arrow.closest("#arrow-button"));


    function replaceIcon(arrow)
    {
      let content = arrow.classList,
          width=35, height=35, color='bs-indigo';

      if (arrow.dataset.hasOwnProperty('width'))
        width = arrow.dataset.width;
      if (arrow.dataset.hasOwnProperty('height'))
        height = arrow.dataset.height;
      if (arrow.dataset.hasOwnProperty('color'))
        color = arrow.dataset.color;

      let arrowDown =
      `<svg class="${color}" width="${width}" height="${height}" viewBox="0 0 1000 1000" xmlns="http://www.w3.org/2000/svg"><path d="M 75 0C 75 0 75 0 75 0C 33 0 0 33 0 75C 0 75 0 925 0 925C 0 967 33 1000 75 1000C 75 1000 925 1000 925 1000C 967 1000 1000 967 1000 925C 1000 925 1000 75 1000 75C 1000 33 967 0 925 0C 925 0 75 0 75 0M 165 565C 165 565 465 265 465 265C 484 245 516 245 535 265C 535 265 835 565 835 565C 855 584 856 616 836 636C 816 656 784 655 765 635C 765 635 500 371 500 371C 500 371 235 635 235 635C 221 650 200 655 181 647C 162 639 150 621 150 601C 150 587 155 574 165 565C 165 565 165 565 165 565" transform="rotate(180,500,500)"></path><title>Expand</title></svg>`,

      arrowUp =
      `<svg class="${color}" width="${width}" height="${height}" viewBox="0 0 1000 1000" xmlns="http://www.w3.org/2000/svg"><path d="M 75 0C 75 0 75 0 75 0C 33 0 0 33 0 75C 0 75 0 925 0 925C 0 967 33 1000 75 1000C 75 1000 925 1000 925 1000C 967 1000 1000 967 1000 925C 1000 925 1000 75 1000 75C 1000 33 967 0 925 0C 925 0 75 0 75 0M 165 565C 165 565 465 265 465 265C 484 245 516 245 535 265C 535 265 835 565 835 565C 855 584 856 616 836 636C 816 656 784 655 765 635C 765 635 500 371 500 371C 500 371 235 635 235 635C 221 650 200 655 181 647C 162 639 150 621 150 601C 150 587 155 574 165 565C 165 565 165 565 165 565"></path><title>Hide</title></svg>`;

      if (arrow.ariaExpanded === 'true')
        arrow.innerHTML = arrowUp;
      else
        arrow.innerHTML = arrowDown;

    }
});
