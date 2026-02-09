// assets/js/store/highlight.js

import { isEmpty as empty }  from '@zerodep/is-empty';

/*
  > Highlight all search terms. (bright yellow background)

  Inspired by https://github.com/0xelsherif/Search-Text-Highlighter
*/
if (document.getElementById('text-highlight') !== null)
{
    let highlightSet = document.querySelectorAll('#text-highlight');

    highlightSet.forEach((highlight, i) =>
    {
      let string = highlight.dataset.searchTerms,
          text  = highlight.dataset.text,
          isExact = highlight.dataset.hasOwnProperty('exact')
          ? highlight.dataset.exact : false,
          keywords, pattern;


      // Option 1: Specify the characters you want to remove
      // keywords = string.replace(/[&\/\\#,+()$~%.'":*?<>{}]/g, '');

      // Option 2: Change all characters except numbers and letters
      keywords = string.
                  // Remove special characters except space
                  // replace(/[^a-zA-Z0-9""''+ ]/g, '').
                  // Strip whitespace from the start and end of a string
                  trim().
                  // Remove unnecessary space in the middle
                  replace(/\s\s+/g, ' ').
                  // Replace all blank space with '|' to separate all words, before passing them in regular expression
                  replace(/\s+/g, '|');

      if (!empty(keywords))
      {
        if (isExact)
          pattern = new RegExp(string, 'gi');
        else
          pattern = new RegExp(keywords, 'gi');

        // Find all matches in text
        let matches = text.match(pattern);

        if (!empty(matches))
        {
          // Highlight matching text in innerHTML
          highlight.innerHTML = text.replace(
            pattern,
            (match) => `<mark class="highlight">${match}</mark>`
          );
        }
      }

      // for (let keyword of keywords)
      // {
      //   // Escape special characters in search query
      //   keyword = keyword.replace(/[.*+?^${}()\[\]\\]/g, "\\$&");
      //
      //   // Create regular expression pattern with search query
      //   let pattern = new RegExp(keyword, "gi");
      //
      //   // Find all matches in text text
      //   let matches = text.match(pattern);
      //   // Check if matches were found
      //   if (matches && matches.length > 0)
      //   {
      //     // Highlight matching text in innerHTML
      //     highlight.innerHTML = highlight.textContent.replace(
      //       pattern,
      //       (match) => `<mark>${match}</mark>`
      //     );
      //   }
      // }
    });
}
