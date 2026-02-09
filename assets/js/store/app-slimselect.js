// assets/js/store/app-slimselect.js

import SlimSelect from 'slim-select';


/* Activate SlimSelect for the exclude color form and
    sumbit its parent form on change. */
if (document.querySelector('#filter_form_color_exclude') !== null)
{
     new SlimSelect({
        select: '#filter_form_color_exclude',
        settings: {
          allowDeselect: false,
          showSearch: true,
          searchText: 'Sorry, couldn\'t find anything',
          placeholderText:'Choose color',
          allowDeselect: true
        },
        events: {
          afterChange: (newVal) => {filter.submit();}
        },
      });
}


/* Activate SlimSelect for the exclude color form and
    sumbit its parent form on change. */
if (document.querySelector('#filter_form_tags') !== null)
{
     new SlimSelect({
        select: '#filter_form_tags',
        settings: {
          allowDeselect: false,
          showSearch: true,
          searchText: 'Sorry, couldn\'t find anything',
          placeholderText:'Choose tags',
          allowDeselect: true
        },
        events: {
          afterChange: (newVal) => {filter.submit();}
        },
      });
}

/* Activate SlimSelect for any <select> that adds 'slim-select' alongside
   the following selectors in the tag itself:
    => data-show-search
    => data-search-msg
    => data-placeholder
    => data-search-highlight
    => data-allow-deselect
 */
if (document.querySelector('[slim-select]') !== null)
{
    let selectTags = document.querySelectorAll('[slim-select]');

    selectTags.forEach((select) =>
    {
      new SlimSelect({
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
    });
}
