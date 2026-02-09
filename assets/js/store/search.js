// assets/js/store/search.js
/*
  Page:  All pages containing search top field.

  Hide the instant results drop-down when the user clicks anywhere
  outside the search input and display it again once he clicks inside.
*/
document.addEventListener(
  'click', e =>
{
  if(e.target.id != 'instant-search')
  {
    let searchResult = document.getElementById('search-results');
    searchResult.style.display = 'none';
  }
  else if (e.target.id == 'instant-search')
  {
    let searchResult = document.getElementById('search-results');
    searchResult.style.display = 'block';
  }
});



/*
  Once the tab is changed, hide the search dropdown.
*/
document.addEventListener(
  'visibilitychange',
  (e) =>
  {
    let searchResult = document.getElementById('search-results');
    /* Change CSS display value from "block" to "none" */
    if (document.visibilityState == "hidden")
      if (searchResult.style.display == 'block')
        searchResult.style.display = 'none';
  });



/*
  When Esc/Escape button is pressed is pressed, hide the search dropdown.
*/
document.addEventListener(
  'keydown',
  (e) =>
  {
    let searchResult = document.getElementById('search-results');
    /* Change CSS display value from "block" to "none" */
    if (e.key === 'Escape' || e.key === 'Esc')
      if (searchResult.style.display == 'block')
        searchResult.style.display = 'none';
  });
