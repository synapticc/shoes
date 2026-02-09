// assets/js/store/toggler.js

import { isEmpty as empty }  from '@zerodep/is-empty';

if (document.querySelector('#data-toggler') !== null)
{
/* When collapsing HTML section (checkbox), change 'Minus icon'
   to 'Plus icon' with its corresponding title.

    title => From 'Show less brands' to 'Show more brands'.

   When expanding HTML section (checkbox), change 'Plus icon'
   to 'Minus icon' with its corresponding title.
*/
let togglerAll = document.querySelectorAll('#data-toggler');

togglerAll.forEach((toggler, i) =>
{
    toggler.addEventListener('click', () =>
    {
      let togglerID, togglerTitle, toggleShow, textMore, textLess;

      // Retrieve dataset values 
      if (toggler.dataset.hasOwnProperty('toggler'))
        togglerID = toggler.dataset.toggler;

      if (toggler.dataset.hasOwnProperty('togglerTitle'))
        togglerTitle = toggler.dataset.togglerTitle;

      // Locate toggler element
      if (!empty(togglerID))
        toggleShow = document.querySelector(togglerID);;

      if (toggler.dataset.hasOwnProperty('textMore'))
        textMore = toggler.dataset.textMore;

      if (toggler.dataset.hasOwnProperty('textLess'))
        textLess = toggler.dataset.textLess;


      let minusToggler = `<svg fill="#636363" width="25" height="25" viewBox="0 0 1000 1000" xmlns="http://www.w3.org/2000/svg"><path d="M 75 0C 75 0 75 0 75 0C 33 0 0 33 0 75C 0 75 0 925 0 925C 0 967 33 1000 75 1000C 75 1000 925 1000 925 1000C 967 1000 1000 967 1000 925C 1000 925 1000 75 1000 75C 1000 33 967 0 925 0C 925 0 75 0 75 0M 75 50C 75 50 925 50 925 50C 939 50 950 61 950 75C 950 75 950 925 950 925C 950 939 939 950 925 950C 925 950 75 950 75 950C 61 950 50 939 50 925C 50 925 50 75 50 75C 50 61 61 50 75 50M 850 475C 850 475 850 525 850 525C 850 525 150 525 150 525C 150 525 150 475 150 475"/><title>Show less ${togglerTitle}</title></svg>`;

      let plusToggler = `<svg fill="#636363" width="25" height="25" viewBox="0 0 1000 1000" xmlns="http://www.w3.org/2000/svg"><path d="M 75 0C 75 0 75 0 75 0C 33 0 0 33 0 75C 0 75 0 925 0 925C 0 967 33 1000 75 1000C 75 1000 925 1000 925 1000C 967 1000 1000 967 1000 925C 1000 925 1000 75 1000 75C 1000 33 967 0 925 0C 925 0 75 0 75 0M 75 50C 75 50 925 50 925 50C 939 50 950 61 950 75C 950 75 950 925 950 925C 950 939 939 950 925 950C 925 950 75 950 75 950C 61 950 50 939 50 925C 50 925 50 75 50 75C 50 61 61 50 75 50M 525 150C 525 150 525 475 525 475C 525 475 850 475 850 475C 850 475 850 525 850 525C 850 525 525 525 525 525C 525 525 525 850 525 850C 525 850 475 850 475 850C 475 850 475 525 475 525C 475 525 150 525 150 525C 150 525 150 475 150 475C 150 475 475 475 475 475C 475 475 475 150 475 150C 475 150 525 150 525 150"/><title>Show more ${togglerTitle}</title></svg>`;


      // Retrieve classes
      let content = toggleShow.classList;

      // Swap classes
      if (content.contains('sidebar-hide'))
      {
        content.replace('sidebar-hide', 'sidebar-reveal');
        toggler.innerHTML = minusToggler;}
      else if(content.contains('sidebar-reveal'))
      {
        content.replace('sidebar-reveal', 'sidebar-hide');
        toggler.innerHTML = plusToggler;}

      /* Change innerHTML when element has expanded and collapsed */
      if (!empty(textMore))
        if (toggler.ariaExpanded === 'true')
          toggler.innerHTML = textLess;
        else
          toggler.innerHTML = textMore;
    });
  });
}
